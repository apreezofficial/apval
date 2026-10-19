<?php

declare(strict_types=1);

namespace App\Http\Controllers\AI;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgrinovaDiseaseController
{
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
            'crop' => 'required|string',
            'location' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'farm_id' => 'nullable|uuid',
        ]);

        $imageFile = $request->file('image');
        $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mimeType = $imageFile->getMimeType();

        $crop = $request->crop;
        $location = $request->location ?? 'Unknown';
        $symptoms = $request->symptoms ?? 'None provided';

        $systemPrompt = "You are an expert agricultural pathologist assisting farmers via Agrinova AI. 
        Analyze the provided image of a '{$crop}' crop. 
        Consider the location: '{$location}' and reported symptoms: '{$symptoms}'.
        
        Respond ONLY in valid JSON format with the following structure:
        {
            \"possible_condition\": \"Name of the likely disease/pest/deficiency\",
            \"confidence\": \"HIGH, MEDIUM, or LOW\",
            \"visible_symptoms\": [\"symptom 1\", \"symptom 2\"],
            \"possible_causes\": [\"cause 1\", \"cause 2\"],
            \"recommended_action\": \"Immediate, actionable steps the farmer can take\",
            \"prevention\": \"How to prevent this in the future\",
            \"requires_expert_review\": true
        }
        If the image is not a plant or is too blurry, set confidence to LOW and state that the image is unclear.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o', // Vision-capable model
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => "Analyze this {$crop} crop image."],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}"
                                ]
                            ]
                        ]
                    ]
                ],
                'response_format' => ['type' => 'json_object'], // Force JSON output
                'max_tokens' => 500,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI Vision Analysis Failed', ['response' => $response->body()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze the image. Please try again.'
                ], 500);
            }

            $result = json_decode($response->json('choices.0.message.content'), true);

            // Log the analysis for audit/expert review (SRS Section 61)
            Log::info('Agrinova Disease Analysis', [
                'crop' => $crop,
                'location' => $location,
                'result' => $result,
            ]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Analysis complete. ' . ($result['requires_expert_review'] ? 'We recommend having a local agronomist verify this.' : '')
            ]);

        } catch (\Exception $e) {
            Log::error('Agrinova Vision Exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while analyzing the image.'
            ], 500);
        }
    }
}