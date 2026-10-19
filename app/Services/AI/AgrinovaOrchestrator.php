<?php
declare(strict_types=1);
namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiToolCall;
use App\Models\AiUsage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI;

class AgrinovaOrchestrator {
    protected $client;
    protected $model;

    // Safety keywords that should trigger warnings (Sec 52, 53)
    private const SAFETY_BLOCKLIST = [
        'human consumption of pesticide', 'inject fertilizer', 'drink chemical',
    ];

    public function __construct() {
        $apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        if (!empty($apiKey)) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function processMessage(string $userId, string $message, ?string $conversationId = null, array $context = []): array {
        $startTime = microtime(true);

        // Safety check (Sec 52)
        if ($this->isUnsafe($message)) {
            return ['success' => false, 'message' => 'Your request contains potentially unsafe content. Please rephrase.'];
        }

        $conversation = $conversationId
            ? AiConversation::findOrFail($conversationId)
            : AiConversation::create(['id' => (string) Str::uuid(), 'user_id' => $userId, 'metadata' => $context]);

        AiMessage::create(['conversation_id' => $conversation->id, 'role' => 'user', 'content' => $message]);

        if (!$this->client) {
            return $this->handleOfflineFallback($conversation, $userId, $message);
        }

        $messages = $this->buildMessageHistory($conversation, $userId, $context);
        $tools = $this->getAvailableTools();

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

            $choice = $response->choices[0];
            $assistantMessage = $choice->message;
            $finalText = $assistantMessage->content;

            if (!empty($assistantMessage->toolCalls)) {
                $toolResults = $this->executeToolCalls($assistantMessage->toolCalls, $userId, $conversation->id);
                $messages[] = ['role' => 'assistant', 'tool_calls' => $this->formatToolCallsForApi($assistantMessage->toolCalls)];
                foreach ($toolResults as $result) {
                    $messages[] = ['role' => 'tool', 'tool_call_id' => $result['tool_call_id'], 'content' => json_encode($result['content'])];
                }
                $finalResponse = $this->client->chat()->create(['model' => $this->model, 'messages' => $messages]);
                $finalText = $finalResponse->choices[0]->message->content;
            }

            $intent = $this->detectIntent($message);
            $conversation->update(['intent_category' => $intent]);

            AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $finalText,
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
            ]);

            // Track usage (Sec 58)
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->trackUsage($userId, $conversation->id, $response, $responseTimeMs, $intent);

            return [
                'success' => true,
                'conversation_id' => $conversation->id,
                'message' => $finalText,
                'intent' => $intent,
                'actions' => $this->extractActions($finalText),
            ];
        } catch (\Exception $e) {
            Log::warning('Agrinova OpenAI Fallback Activated', ['error' => $e->getMessage()]);
            return $this->handleOfflineFallback($conversation, $userId, $message);
        }
    }

    /**
     * Resilient built-in fallback knowledge assistant for AgreonPay & Agriculture.
     */
    private function handleOfflineFallback(AiConversation $conversation, string $userId, string $message): array {
        $lower = strtolower(trim($message));
        $reply = "";
        $intent = "general_advice";

        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening|howdy)\b/', $lower)) {
            $reply = "🌾 Hello! I am Agrinova AI, your agricultural and fintech assistant on AgreonPay.\n\nHow can I support your farming or shopping journey today? You can ask me about:\n• Crop management, soil, and pest prevention\n• Real-time marketplace tips and orders\n• Funding your wallet, virtual accounts, and withdrawals\n• Agricultural loan options & AgroPoints";
            $intent = "greeting";
        } elseif (preg_match('/\b(maize|corn)\b/', $lower)) {
            $reply = "🌽 **Maize Farming Tips:**\n• **Soil:** Well-drained loamy soil with pH 5.8 – 6.8.\n• **Planting:** Plant at 75cm between rows and 25cm between plants (2-3 seeds per hole, thin to 1).\n• **Fertilizer:** Apply NPK 15:15:15 at 2 weeks after planting, followed by Urea top-dressing at 5-6 weeks.\n• **Pest Alert:** Watch out for Fall Armyworm. Apply Neem extract or approved insecticides early in the morning.";
            $intent = "crop_guidance";
        } elseif (preg_match('/\b(cassava)\b/', $lower)) {
            $reply = "🌿 **Cassava Cultivation Guidance:**\n• **Cuttings:** Use healthy 20-25cm stem cuttings with 4-6 nodes.\n• **Planting:** Angle cuttings at 45 degrees or plant flat in moist, loosened ridges.\n• **Weed Control:** Critical within the first 3 months until canopy closure.\n• **Harvest:** Typically ready 10-12 months after planting.";
            $intent = "crop_guidance";
        } elseif (preg_match('/\b(rice)\b/', $lower)) {
            $reply = "🌾 **Rice Production Essentials:**\n• **Varieties:** FARO 44, FARO 52, or upland varieties based on field water access.\n• **Spacing:** 20cm x 20cm transplanting or direct broadcasting.\n• **Water Management:** Maintain 3-5cm water depth during vegetative phase for lowland paddy.\n• **Fertilization:** Split application of NPK and Urea for maximum tillering.";
            $intent = "crop_guidance";
        } elseif (preg_match('/\b(tomato|pepper|vegetable)\b/', $lower)) {
            $reply = "🍅 **Vegetable & Tomato Care:**\n• **Nursery:** Raise seedlings in nursery trays for 3-4 weeks before transplanting.\n• **Watering:** Drip irrigation or morning watering directly to roots (avoid wetting foliage to prevent blight).\n• **Staking:** Stake indeterminate tomatoes to improve airflow and fruit quality.\n• **Pest Control:** Use sticky yellow traps for whiteflies and aphids.";
            $intent = "crop_guidance";
        } elseif (preg_match('/\b(pest|disease|worm|insect|blight|fungus)\b/', $lower)) {
            $reply = "🛡️ **Pest & Disease Management:**\n• Early identification is key to preventing crop loss.\n• For caterpillars/armyworms: use bio-pesticides (Bacillus thuringiensis) or Neem oil.\n• For fungal leaf spots: ensure proper field drainage, crop rotation, and avoid overhead watering.\n• Need expert inspection? You can raise a support ticket or request an agronomist visit on AgreonPay.";
            $intent = "disease_diagnostics";
        } elseif (preg_match('/\b(wallet|fund|deposit|money|bank|transfer)\b/', $lower)) {
            $reply = "💳 **AgreonPay Wallet & Payments:**\n• To fund your wallet: Tap **Fund Wallet** on your Home screen to copy your dedicated Monnify Virtual Account number, or pay via debit card.\n• Transfers to bank accounts are processed instantly with 2FA / PIN protection.\n• All marketplace transactions use AgreonPay **Escrow** to protect both buyers and sellers.";
            $intent = "wallet_support";
        } elseif (preg_match('/\b(loan|credit|borrow)\b/', $lower)) {
            $reply = "🏦 **AgricNova Loan Facilities:**\n• AgreonPay provides seasonal input financing and working capital loans for verified farmers and agro-dealers.\n• Requirements: Completed Tier 1 / BVN verification, active transaction history, and farm location verification.\n• Check the **Loans** tab on your dashboard to see your current pre-qualified limit!";
            $intent = "loan_inquiry";
        } elseif (preg_match('/\b(kyc|verify|bvn|identity)\b/', $lower)) {
            $reply = "🛡️ **KYC Verification:**\n• Verifying your identity unlocks higher wallet limits and access to loan features.\n• To complete KYC: Go to **Profile** → **Identity Verification** and enter your BVN.\n• Verification is instant and automated.";
            $intent = "kyc_inquiry";
        } else {
            $reply = "🌾 **Agrinova AI Assistance:**\n\nI understand your query about: *\"" . htmlspecialchars(substr($message, 0, 100)) . "\"*.\n\nHere are some of the things I can help you with:\n1. 🚜 **Crops & Cultivation**: Planting calendars, fertilizer ratios, spacing, harvesting.\n2. 🐛 **Pest Management**: Identifying and treating pests, organic solutions.\n3. 💰 **AgreonPay Platform**: Managing your wallet, escrow payments, AgroPoints, and loans.\n4. 🛒 **Marketplace**: Sourcing produce, selling farm harvests, and tracking deliveries.\n\nPlease feel free to specify your crop or question for tailored advice!";
        }

        $intentCat = $this->detectIntent($message);
        $conversation->update(['intent_category' => $intentCat]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
            'prompt_tokens' => 20,
            'completion_tokens' => 80,
        ]);

        return [
            'success' => true,
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'intent' => $intent,
            'actions' => $this->extractActions($reply),
        ];
    }

    private function isUnsafe(string $message): bool {
        $lower = strtolower($message);
        foreach (self::SAFETY_BLOCKLIST as $keyword) {
            if (str_contains($lower, $keyword)) return true;
        }
        return false;
    }

        private function buildMessageHistory(AiConversation $conversation, string $userId, array $context, string $source = 'standalone'): array
    {
        $basePrompt = "You are Agrinova AI, the intelligence engine of AgreonPay. Tagline: 'Intelligence from Farm to Table.'";
        
        // 1. Inject farm context if available (Do this FIRST so it can be appended to the prompt)
        $farmContext = '';
        if (!empty($context['farm_id'])) {
            $farmData = app(\App\Services\AI\FarmProfileTool::class)->getFarmProfile($userId, $context['farm_id']);
            if ($farmData['found'] ?? false) {
                $farmContext = "\n\nUSER'S FARM PROFILE:\n" . json_encode($farmData['farm']);
            }
        }

        // 2. Build the system prompt based on the source, appending farm context to both
        if ($source === 'chat') {
            $systemPrompt = "{$basePrompt} You are currently integrated into the AgreonPay Chatroom. 
            Keep responses concise, conversational, and highly actionable. 
            If asked about products, prices, or availability, you MUST use the search_marketplace_products tool. 
            If asked about farming costs, use calculate_farm_cost. 
            NEVER fabricate live prices or inventory. 
            Encourage users to use AgreonPay's secure escrow for transactions.{$farmContext}";
        } else {
            $systemPrompt = "{$basePrompt} You assist with: (1) Agriculture — crops, fertilizer, weather, farm planning, costs, disease. (2) Food — meal planning, recipes, nutrition, budgets. (3) AgreonPay — marketplace search, orders, logistics.
            
            RULES:
            1. NEVER fabricate live prices, inventory, or weather. Always use the provided tools.
            2. NEVER execute transactions (payments, cart additions). Recommend actions and wait for user confirmation.
            3. Communicate uncertainty. Never make unsupported medical or pesticide claims.
            4. Keep responses concise, actionable, and formatted clearly.{$farmContext}";
        }

        // 3. Initialize messages array with the system prompt
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        
        // 4. Append conversation history (last 10 messages to maintain context without blowing up tokens)
        $history = $conversation->messages()->latest()->take(10)->get()->reverse();
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role, 
                'content' => $msg->content
            ];
        }

        return $messages;
    }

    private function getAvailableTools(): array {
        return [
            ['type' => 'function', 'function' => [
                'name' => 'search_marketplace_products',
                'description' => 'Search for live agricultural or food products in the AgreonPay marketplace.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'query' => ['type' => 'string'],
                    'max_price' => ['type' => 'number'],
                ], 'required' => ['query']],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'calculate_farm_cost',
                'description' => 'Calculate estimated production costs, yield, and revenue for a crop.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'crop' => ['type' => 'string'],
                    'farm_size_hectares' => ['type' => 'number'],
                    'inputs' => ['type' => 'array', 'items' => ['type' => 'string']],
                ], 'required' => ['crop', 'farm_size_hectares']],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'get_farm_profile',
                'description' => 'Retrieve the user\'s farm profile (location, size, crop, soil).',
                'parameters' => ['type' => 'object', 'properties' => [
                    'farm_id' => ['type' => 'string', 'description' => 'Optional specific farm ID'],
                ]],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'get_weather',
                'description' => 'Get current weather and agricultural advice for a location.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'location' => ['type' => 'string', 'description' => 'City, state, or region'],
                ], 'required' => ['location']],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'generate_farming_calendar',
                'description' => 'Generate a week-by-week farming calendar for a crop.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'crop' => ['type' => 'string'],
                    'planting_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                ], 'required' => ['crop', 'planting_date']],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'generate_meal_plan',
                'description' => 'Generate a family meal plan based on budget and family size.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'people' => ['type' => 'integer'],
                    'adults' => ['type' => 'integer'],
                    'children' => ['type' => 'integer'],
                    'days' => ['type' => 'integer'],
                    'budget' => ['type' => 'number'],
                    'currency' => ['type' => 'string'],
                ], 'required' => ['people', 'days', 'budget']],
            ]],
            ['type' => 'function', 'function' => [
                'name' => 'create_shopping_list',
                'description' => 'Convert a meal plan into a categorized shopping list.',
                'parameters' => ['type' => 'object', 'properties' => [
                    'meal_plan' => ['type' => 'array', 'description' => 'The meal plan array'],
                ], 'required' => ['meal_plan']],
            ]],
        ];
    }

    private function executeToolCalls(array $toolCalls, string $userId, string $conversationId): array {
        $results = [];
        foreach ($toolCalls as $toolCall) {
            $startTime = microtime(true);
            $functionName = $toolCall->function->name;
            $arguments = json_decode($toolCall->function->arguments, true) ?? [];
            $success = true;
            $error = null;
            $content = [];

            try {
                $content = match ($functionName) {
                    'search_marketplace_products' => app(MarketplaceSearchTool::class)->search($arguments['query'], $arguments['max_price'] ?? null),
                    'calculate_farm_cost' => app(FarmCostCalculatorTool::class)->calculate($arguments),
                    'get_farm_profile' => app(FarmProfileTool::class)->getFarmProfile($userId, $arguments['farm_id'] ?? null),
                    'get_weather' => app(WeatherTool::class)->getWeather($arguments['location']),
                    'generate_farming_calendar' => app(FarmingCalendarTool::class)->generateCalendar($arguments['crop'], $arguments['planting_date']),
                    'generate_meal_plan' => app(MenuPlannerTool::class)->generateMealPlan($arguments),
                    'create_shopping_list' => app(ShoppingListTool::class)->createShoppingList($userId, $arguments['meal_plan']),
                    default => ['error' => 'Tool not implemented'],
                };
            } catch (\Exception $e) {
                $success = false;
                $error = $e->getMessage();
                $content = ['error' => 'Tool execution failed'];
                Log::error('Agrinova Tool Error', ['tool' => $functionName, 'error' => $e->getMessage()]);
            }

            $executionTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            // Audit log for tool calls (Sec 61)
            AiToolCall::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversationId,
                'tool_name' => $functionName,
                'arguments' => $arguments,
                'result' => $content,
                'execution_time_ms' => $executionTimeMs,
                'success' => $success,
                'error_message' => $error,
            ]);

            $results[] = ['tool_call_id' => $toolCall->id, 'content' => $content];
        }
        return $results;
    }

    private function formatToolCallsForApi($toolCalls): array {
        $formatted = [];
        foreach ($toolCalls as $tc) {
            $formatted[] = [
                'id' => $tc->id,
                'type' => 'function',
                'function' => ['name' => $tc->function->name, 'arguments' => $tc->function->arguments],
            ];
        }
        return $formatted;
    }

    private function detectIntent(string $message): string {
        $lower = strtolower($message);
        $agriKeywords = ['crop', 'fertilizer', 'plant', 'harvest', 'soil', 'pest', 'disease', 'farm', 'weather', 'maize', 'rice', 'tomato'];
        $foodKeywords = ['recipe', 'cook', 'meal', 'menu', 'food', 'nutrition', 'budget', 'shopping', 'breakfast', 'lunch', 'dinner'];
        $agreonpayKeywords = ['buy', 'sell', 'cart', 'order', 'pay', 'delivery', 'vendor', 'marketplace', 'product'];

        foreach ($agriKeywords as $kw) if (str_contains($lower, $kw)) return 'agriculture';
        foreach ($foodKeywords as $kw) if (str_contains($lower, $kw)) return 'food';
        foreach ($agreonpayKeywords as $kw) if (str_contains($lower, $kw)) return 'agreonpay';
        return 'general';
    }

    private function extractActions(string $text): array {
        $actions = [];
        if (stripos($text, 'add to cart') !== false) $actions[] = 'add_to_cart_pending';
        if (stripos($text, 'proceed to payment') !== false) $actions[] = 'payment_pending';
        if (stripos($text, 'view product') !== false) $actions[] = 'view_product';
        return $actions;
    }

    private function trackUsage(string $userId, string $conversationId, $response, int $responseTimeMs, string $intent): void {
        $promptTokens = $response->usage->promptTokens;
        $completionTokens = $response->usage->completionTokens;
        $totalTokens = $promptTokens + $completionTokens;
        // Rough cost estimate: gpt-4o-mini input $0.15/1M, output $0.60/1M
        $cost = ($promptTokens * 0.00000015) + ($completionTokens * 0.00000060);

        AiUsage::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'model' => $this->model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'estimated_cost_usd' => $cost,
            'response_time_ms' => $responseTimeMs,
            'success' => true,
            'intent_category' => $intent,
        ]);
    }
}