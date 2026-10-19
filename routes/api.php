<?php

use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\AccountResolutionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\MonnifyWebhookController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Http\Request;
use App\Http\Controllers\AI\AgrinovaChatController;
use App\Http\Controllers\AI\AgrinovaDiseaseController; 
use App\Http\Controllers\AI\AgrinovaFarmController;
use App\Http\Controllers\AI\AgrinovaFeedbackController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Commerce\VendorProductController;
use App\Http\Controllers\Commerce\MarketplaceProductController;
use App\Http\Controllers\Commerce\VendorDashboardController;
use App\Http\Controllers\Commerce\VendorProfileController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\OrderController;
use App\Http\Controllers\Commerce\VendorOrderController;
use App\Http\Controllers\Commerce\InspectionController;
use App\Http\Controllers\Commerce\TrackingController;
use App\Http\Controllers\DispatchWebhookController; 
use App\Http\Controllers\MediaController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AgroPointsController;
use App\Http\Controllers\Commerce\SplitBuyController;
use App\Http\Controllers\Commerce\DisputeController;
use App\Http\Controllers\Commerce\PaymentMethodController;
use App\Http\Controllers\Commerce\PromoCodeController;
use App\Http\Controllers\Commerce\WishlistController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\KycAdminController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\Admin\LogisticsAdminController;
use App\Http\Controllers\Admin\SupportTicketAdminController;
//chat
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\CommunityController as ChatCommunityController;
use App\Http\Controllers\Chat\ModerationController;
//Feed
use App\Http\Controllers\Feed\FeedController;
use App\Http\Controllers\Feed\PostController;
use App\Http\Controllers\Feed\InteractionController;
use App\Http\Controllers\Feed\AdminFeedController;
use App\Http\Controllers\Feed\NotificationController;
// Ads
use App\Http\Controllers\Admin\Ads\AdminAdPackageController;
use App\Http\Controllers\Admin\Ads\AdminAdPlacementController;
use App\Http\Controllers\Admin\Ads\AdminAdPricingController;
use App\Http\Controllers\Admin\Ads\AdminAdRefundController;
use App\Http\Controllers\Admin\Ads\AdminAdRevenueController;
use App\Http\Controllers\Admin\Ads\AdminAdSettingController;
use App\Http\Controllers\Admin\Ads\AdminAdvertisementController;
use App\Http\Controllers\Admin\Ads\AdminAdvertiserController;
use App\Http\Controllers\Ads\AdMediaController;
use App\Http\Controllers\Ads\AdPaymentController;
use App\Http\Controllers\Ads\AdReportController;
use App\Http\Controllers\Ads\AdTrackingController;
use App\Http\Controllers\Ads\AdvertisementController;
use App\Http\Controllers\Ads\AdvertiserController;
use App\Http\Controllers\Ads\AdWalletController;

use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\NotificationFeedController;
use App\Http\Controllers\Commerce\ProductReviewController;

// ============================================
// MARKET AGENT MODULE ROUTES
// ============================================
use App\Http\Controllers\Agent\AgentProfileController;
use App\Http\Controllers\Agent\AgentAuthController;
use App\Http\Controllers\Agent\LeadController;
use App\Http\Controllers\Agent\ActivationPaymentController;
use App\Http\Controllers\Agent\AgentDailyReportController;
use App\Http\Controllers\Agent\AgentKpiController;
use App\Http\Controllers\Agent\AgentEarningController;
use App\Http\Controllers\Admin\AgentAdminController;
use App\Http\Controllers\Admin\LeadAdminController;
use App\Http\Controllers\Admin\AgentEarningAdminController;

Route::get('/user', function (Request $request) {
    return $request->user()->only([
        'id', 'first_name', 'last_name', 'email', 'phone',
        'role', 'status', 'market', 'created_at',
    ]);
})->middleware('auth:sanctum');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

//user management routes
Route::prefix('marketAgent')->group(function () {
    // Public auth routes — rate limited to prevent brute-force / mass registration
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register',            [AuthController::class, 'register']);
        Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOtp']);
        Route::post('/register/resend-otp', [AuthController::class, 'resendRegistrationOtp']);
        Route::post('/login',               [AuthController::class, 'login']);
        Route::post('/forgot-password',     [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password',      [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,10');
        Route::get('/me',               [AuthController::class, 'me']);
        Route::get('/profile',          [UserProfileController::class, 'show']);
        Route::match(['put', 'post'], '/profile', [UserProfileController::class, 'update']);
        Route::delete('/delete-account',[AccountDeletionController::class, 'deleteAuthenticatedAccount']);
        Route::post('/2fa/enable',       [TwoFactorController::class, 'enable']);
        Route::post('/2fa/request-otp',  [TwoFactorController::class, 'requestOtp'])->middleware('throttle:5,1');
        Route::post('/2fa/resend-otp',   [TwoFactorController::class, 'resendOtp'])->middleware('throttle:3,1');
        Route::post('/2fa/verify-otp',   [TwoFactorController::class, 'verifyOtp'])->middleware('throttle:5,1');
        Route::post('/pin/request-otp',  [PinController::class, 'requestOtp'])->middleware('throttle:5,1');
        Route::post('/pin/set',          [PinController::class, 'set']);
        Route::post('/pin/change',       [PinController::class, 'change'])->middleware('throttle:5,10');
        Route::post('/biometric/enable', [PinController::class, 'enableBiometric']);
        Route::post('/biometric/disable',[PinController::class, 'disableBiometric']);

        // KYC self-service verification
        Route::get('/kyc',     [KycController::class, 'show']);
        Route::post('/kyc',    [KycController::class, 'store'])->middleware('throttle:3,1');
        Route::post('/kyc/upgrade', [KycController::class, 'upgrade'])->middleware('throttle:3,1');

        // Referral routes
        Route::get('/referral', [ReferralController::class, 'show']);

        // FCM Device Push Token routes
        Route::post('/device-token',   [DeviceTokenController::class, 'store']);
        Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
    });
});

// Top-level profile & KYC route aliases (for direct /api/* callers)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',                           [AuthController::class, 'me']);
    Route::get('/profile',                      [UserProfileController::class, 'show']);
    Route::match(['put', 'post'], '/profile',   [UserProfileController::class, 'update']);
    Route::get('/kyc',                          [KycController::class, 'show']);
    Route::post('/kyc',                         [KycController::class, 'store'])->middleware('throttle:3,1');
    Route::post('/kyc/upgrade',                 [KycController::class, 'upgrade'])->middleware('throttle:3,1');
});

// Loans & Credit Facility routes
Route::middleware('auth:sanctum')->prefix('loans')->group(function () {
    Route::get('/',             [LoanController::class, 'dashboard']);
    Route::post('/apply',       [LoanController::class, 'apply'])->middleware('throttle:5,1');
    Route::get('/{id}',         [LoanController::class, 'show']);
    Route::post('/{id}/repay',  [LoanController::class, 'repay'])->middleware('throttle:10,1');
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // KYC review queue
    Route::get('/kyc',              [KycAdminController::class, 'index']);
    Route::get('/kyc/pending',      [KycAdminController::class, 'pending']);
    Route::post('/kyc/{id}/approve',[KycAdminController::class, 'approve']);
    Route::post('/kyc/{id}/reject', [KycAdminController::class, 'reject']);

    // Support ticket management
    Route::get('/support/tickets',           [SupportTicketAdminController::class, 'index']);
    Route::get('/support/tickets/{id}',      [SupportTicketAdminController::class, 'show']);
    Route::put('/support/tickets/{id}/status', [SupportTicketAdminController::class, 'updateStatus']);
    Route::post('/support/tickets/{id}/reply',  [SupportTicketAdminController::class, 'reply']);

    // Logistics Dashboard Metrics 
    Route::get('/logistics/dashboard', [LogisticsAdminController::class, 'dashboard']);

});


Route::middleware(['auth:sanctum'])->prefix('walletManager')->group(function () {
    Route::get('/balances',    [WalletController::class, 'balances']);
    Route::get('/transactions',[WalletController::class, 'transactions']);
    Route::post('/transfer',   [WalletController::class, 'transferInternal'])->middleware('throttle:30,1');
    Route::post('/virtual-accounts/ngn', [WalletController::class, 'getOrCreateNgnVirtualAccount']);
    Route::post('/withdrawals/initiate', [WithdrawalController::class, 'initiate'])->middleware('throttle:10,1');
    Route::post('/withdrawals/{reference}/confirm-2fa', [WithdrawalController::class, 'confirm2fa'])
        ->middleware('throttle:5,10');
    Route::get('/banks',          [BankController::class, 'index']);
    Route::post('/resolve-account',[AccountResolutionController::class, 'resolve']);
    Route::post('/suggest-banks',  [AccountResolutionController::class, 'suggest']);

    // Agro Points routes
    Route::get('/points',          [AgroPointsController::class, 'show']);
    Route::post('/points/redeem',  [AgroPointsController::class, 'redeem']);
});

// Commerce routes
Route::prefix('commerce')->group(function () {
    // Public Marketplace routes
    Route::get('/marketplace/products', [MarketplaceProductController::class, 'index']);
    Route::get('/marketplace/categories', [MarketplaceProductController::class, 'categories']);
    Route::get('/marketplace/products/{id}', [MarketplaceProductController::class, 'show']);
    Route::get('/marketplace/products/{id}/share-card', [MarketplaceProductController::class, 'shareCard']);
    Route::get('/marketplace/products/{id}/reviews', [ProductReviewController::class, 'index']);
    Route::get('/vendors/{id}', [VendorProfileController::class, 'publicStore']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Vendor routes (require vendor or admin role)
        Route::middleware('role:vendor,admin')->group(function () {
            Route::get('/vendor/products', [VendorProductController::class, 'index']);
            Route::post('/vendor/products', [VendorProductController::class, 'store']);
            Route::get('/vendor/products/{id}', [VendorProductController::class, 'show']);
            Route::put('/vendor/products/{id}', [VendorProductController::class, 'update']);
            Route::delete('/vendor/products/{id}', [VendorProductController::class, 'destroy']);
            Route::post('/vendor/products/{id}/inventory', [VendorProductController::class, 'updateInventory']);

            // Dashboard routes
            Route::get('/vendor/dashboard/overview', [VendorDashboardController::class, 'overview']);
            Route::get('/vendor/dashboard/stats', [VendorDashboardController::class, 'overview']);
            Route::get('/vendor/dashboard/transactions', [VendorDashboardController::class, 'transactions']);

            // Settings routes
            Route::get('/vendor/settings', [VendorProfileController::class, 'show']);
            Route::get('/vendor/setting', [VendorProfileController::class, 'show']);
            Route::get('/vendor/store/profile', [VendorProfileController::class, 'show']);
            Route::put('/vendor/settings', [VendorProfileController::class, 'update']);
            Route::put('/vendor/setting', [VendorProfileController::class, 'update']);
            Route::put('/vendor/store/profile', [VendorProfileController::class, 'update']);

            // Vendor Order Processing routes
            Route::prefix('vendor/orders')->group(function () {
                Route::get('/', [VendorOrderController::class, 'index']);
                Route::put('/{itemId}/accept', [VendorOrderController::class, 'accept']);
                Route::put('/{itemId}/reject', [VendorOrderController::class, 'reject']);
                Route::put('/{itemId}/ready', [VendorOrderController::class, 'markReady']);
                Route::put('/{itemId}/dispatch', [VendorOrderController::class, 'dispatch']);
            });
        });

        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            Route::prefix('admin/inspections')->group(function () {
                Route::post('/assign', [InspectionController::class, 'assign']);
            });
        });

        // Inspector routes (require inspector or admin role)
        Route::middleware('role:inspector,admin')->group(function () {
            Route::prefix('inspector/inspections')->group(function () {
                Route::post('/{id}/submit', [InspectionController::class, 'submit']);
            });
        });

        // Buyer Cart routes (all authenticated users can buy)
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::post('/update', [CartController::class, 'update']);
            Route::delete('/remove', [CartController::class, 'remove']);
            Route::delete('/clear', [CartController::class, 'clear']);
        });

        // Buyer Order routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/checkout', [OrderController::class, 'checkout']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
            Route::put('/items/{itemId}/confirm-delivery', [OrderController::class, 'confirmDelivery']);
            Route::get('/items/{itemId}/track', [TrackingController::class, 'show']);
            Route::post('/items/{itemId}/reviews', [ProductReviewController::class, 'store']);
        });

        // Split Buy (Group Purchasing) routes
        Route::prefix('split-buy')->group(function () {
            Route::post('/', [SplitBuyController::class, 'store']);
            Route::get('/{id}', [SplitBuyController::class, 'show']);
            Route::post('/{id}/contribute', [SplitBuyController::class, 'contribute']);
        });

        // Disputes routes
        Route::prefix('disputes')->group(function () {
            Route::get('/', [DisputeController::class, 'index']);
            Route::post('/', [DisputeController::class, 'store']);
            Route::get('/{id}', [DisputeController::class, 'show']);
            Route::post('/{id}/messages', [DisputeController::class, 'addMessage']);
        });

        // Payment Methods routes
        Route::prefix('payment-methods')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index']);
            Route::post('/', [PaymentMethodController::class, 'store']);
            Route::put('/{id}', [PaymentMethodController::class, 'update']);
            Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
        });

        // Promo Codes routes
        Route::prefix('promos')->group(function () {
            Route::get('/', [PromoCodeController::class, 'index']);
            Route::post('/apply', [PromoCodeController::class, 'apply']);
        });

        // Wishlist routes
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/', [WishlistController::class, 'store']);
            Route::delete('/{productId}', [WishlistController::class, 'destroy']);
        });
    });
});


// Webhook routes (public — validated by HMAC signature)
Route::post('/monnify', [MonnifyWebhookController::class, 'handle'])
    ->middleware('throttle:60,1');

Route::post('/areaconnect/webhook', [DispatchWebhookController::class, 'handle'])
    ->middleware('throttle:60,1');

// ✅ AGRINOVA AI ROUTES 
Route::middleware(['auth:sanctum', 'throttle:30,1'])->prefix('v1/agrinova')->group(function () {
    // Core AI Chat
    Route::post('/chat', [AgrinovaChatController::class, 'chat']);
    
    // Disease Analysis 
    Route::post('/disease/analyze', [AgrinovaDiseaseController::class, 'analyze']);
    
    // Farm Management 
    Route::get('/farm', [AgrinovaFarmController::class, 'index']);
    Route::post('/farm', [AgrinovaFarmController::class, 'store']);
    Route::get('/farm/{id}', [AgrinovaFarmController::class, 'show']);
    
    // AI Feedback 
    Route::post('/feedback', [AgrinovaFeedbackController::class, 'submit']);
});


// Media routes
Route::middleware('auth:sanctum')->post('/media/upload', [MediaController::class, 'upload']);

// Chat routes
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
    Route::get('/conversations',                      [ChatController::class, 'index']);
    Route::post('/conversations',                     [ChatController::class, 'startConversation']);
    Route::post('/conversations/direct',              [ChatController::class, 'startConversation']);
    Route::post('/conversations/marketplace',         [ChatController::class, 'marketplaceChat']);
    Route::post('/conversations/order',               [ChatController::class, 'orderChat']);
    Route::post('/conversations/support',             [ChatController::class, 'supportChat']);
    Route::get('/conversations/{id}/messages',        [ChatController::class, 'messages']);
    Route::post('/conversations/{id}/messages',       [ChatController::class, 'sendMessage']);
    Route::post('/conversations/{id}/read',           [ChatController::class, 'markAsRead']);
    Route::post('/messages/{messageId}/react',        [ChatController::class, 'react']);
    Route::post('/messages/{messageId}/pin',          [ChatController::class, 'pin']);
    Route::delete('/messages/{messageId}',            [ChatController::class, 'destroyMessage']);
    Route::post('/block',                             [ChatController::class, 'block']);
    Route::post('/unblock',                           [ChatController::class, 'unblock']);
    Route::post('/report',                            [ChatController::class, 'report']);
});

// Community routes
Route::middleware('auth:sanctum')->prefix('communities')->group(function () {
    Route::get('/',                                   [CommunityController::class, 'index']);
    Route::post('/',                                  [CommunityController::class, 'store']);
    Route::get('/{id}',                               [CommunityController::class, 'show']);
    Route::post('/{id}/join',                         [CommunityController::class, 'join']);
    Route::post('/{id}/leave',                        [CommunityController::class, 'leave']);
    Route::post('/{id}/polls',                        [CommunityController::class, 'createPoll']);
    Route::post('/polls/{pollId}/vote',               [CommunityController::class, 'votePoll']);
    Route::put('/{id}/members/{userId}/role',         [CommunityController::class, 'updateRole']);
    Route::post('/{id}/members/{userId}/mute',        [CommunityController::class, 'mute']);
});

// Support ticket user routes
Route::middleware('auth:sanctum')->prefix('support/tickets')->group(function () {
    Route::get('/',            [SupportTicketController::class, 'index']);
    Route::post('/',           [SupportTicketController::class, 'store']);
    Route::get('/{id}',        [SupportTicketController::class, 'show']);
    Route::post('/{id}/reply', [SupportTicketController::class, 'reply']);
});



// ============================================
// CHATROOM ROUTES
// ============================================
Route::middleware(['auth:sanctum'])->prefix('v1/chat')->group(function () {
    
    // Conversation Routes
    Route::prefix('conversations')->group(function () {
        // List conversations
        Route::get('/', [ConversationController::class, 'index']);
        
        // Get conversation details
        Route::get('/{id}', [ConversationController::class, 'show']);
        
        // Start new conversations
        Route::post('/direct', [ConversationController::class, 'startDirect']);
        Route::post('/marketplace', [ConversationController::class, 'startMarketplace']);
        Route::post('/group', [ConversationController::class, 'createGroup']);
        
        // Manage participants
        Route::post('/{id}/participants', [ConversationController::class, 'addParticipant']);
        Route::delete('/{id}/participants/{userId}', [ConversationController::class, 'removeParticipant']);
        
        // Leave/Archive
        Route::post('/{id}/leave', [ConversationController::class, 'leave']);
        Route::post('/{id}/archive', [ConversationController::class, 'archive']);
        Route::post('/{id}/unarchive', [ConversationController::class, 'unarchive']);
    });

    // Message Routes
    Route::prefix('conversations/{conversationId}/messages')->group(function () {
        // Get messages
        Route::get('/', [MessageController::class, 'index']);
        
        // Send messages
        Route::post('/text', [MessageController::class, 'sendText']);
        Route::post('/media', [MessageController::class, 'sendMedia']);
        Route::post('/product', [MessageController::class, 'shareProduct']);
        Route::post('/order', [MessageController::class, 'shareOrder']);
        Route::post('/location', [MessageController::class, 'sendLocation']);
        
        // Mark as read
        Route::post('/read', [MessageController::class, 'markAsRead']);
        Route::post('/typing', [MessageController::class, 'startTyping']);
        Route::post('/stop-typing', [MessageController::class, 'stopTyping']);
        Route::post('/agrionva-mention', [MessageController::class, 'triggerAgrinova']);
    });

    // Message Actions (individual message operations)
    Route::prefix('messages')->group(function () {
        // Reactions
        Route::post('/{id}/react', [MessageController::class, 'addReaction']);
        Route::delete('/{id}/react', [MessageController::class, 'removeReaction']);
        
        // Edit/Delete
        Route::put('/{id}', [MessageController::class, 'edit']);
        Route::delete('/{id}', [MessageController::class, 'destroy']);
        
        // Forward
        Route::post('/{id}/forward', [MessageController::class, 'forward']);
    });

    // Community Routes
    Route::prefix('communities')->group(function () {
        // Discover communities
        Route::get('/discover', [ChatCommunityController::class, 'discover']);
        
        // My communities
        Route::get('/my', [ChatCommunityController::class, 'myCommunities']);
        
        // Community details
        Route::get('/{id}', [ChatCommunityController::class, 'show']);
        
        // Create community
        Route::post('/', [ChatCommunityController::class, 'store']);
        
        // Join/Leave
        Route::post('/{id}/join', [ChatCommunityController::class, 'join']);
        Route::post('/{id}/leave', [ChatCommunityController::class, 'leave']);
        
        // Update community (admin)
        Route::put('/{id}', [ChatCommunityController::class, 'update']);
        
        // Members
        Route::get('/{id}/members', [ChatCommunityController::class, 'members']);
        
        // Member management (admin/moderator)
        Route::post('/{id}/members/{userId}/ban', [ChatCommunityController::class, 'banMember']);
        Route::post('/{id}/members/{userId}/unban', [ChatCommunityController::class, 'unbanMember']);
        Route::post('/{id}/members/{userId}/mute', [ChatCommunityController::class, 'muteMember']);
        Route::post('/{id}/members/{userId}/unmute', [ChatCommunityController::class, 'unmuteMember']);
        Route::post('/{id}/members/{userId}/promote', [ChatCommunityController::class, 'promoteMember']);
        Route::post('/{id}/members/{userId}/demote', [ChatCommunityController::class, 'demoteMember']);
        
        // Delete community (owner)
        Route::delete('/{id}', [ChatCommunityController::class, 'destroy']);
    });

    // Moderation Routes
    Route::prefix('reports')->group(function () {
        // Report message
        Route::post('/message', [ModerationController::class, 'reportMessage']);
        
        // Report user
        Route::post('/user', [ModerationController::class, 'reportUser']);
    });

    // Block Routes
    Route::prefix('blocks')->group(function () {
        // Block user
        Route::post('/', [ModerationController::class, 'blockUser']);
        
        // Unblock user
        Route::delete('/{userId}', [ModerationController::class, 'unblockUser']);
        
        // Get blocked users
        Route::get('/', [ModerationController::class, 'getBlockedUsers']);
    });
});


// ============================================
// NEWS FEED ROUTES
// ============================================
Route::middleware(['auth:sanctum'])->prefix('v1/feed')->group(function () {
    // Main Feed
    Route::get('/', [FeedController::class, 'index']);
    Route::get('/trending', [FeedController::class, 'trending']);
    Route::get('/search', [FeedController::class, 'search']);
    Route::get('/saved', [FeedController::class, 'saved']);
    Route::get('/category/{categoryId}', [FeedController::class, 'byCategory']);
    Route::get('/hashtag/{hashtag}', [FeedController::class, 'byHashtag']);

    // Posts
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/posts/user/{userId}', [PostController::class, 'userPosts']);

    // Interactions - Likes
    Route::post('/posts/{id}/like', [InteractionController::class, 'likePost']);
    Route::delete('/posts/{id}/like', [InteractionController::class, 'unlikePost']);
    Route::post('/comments/{id}/like', [InteractionController::class, 'likeComment']);
    Route::delete('/comments/{id}/like', [InteractionController::class, 'unlikeComment']);

    // Interactions - Comments
    Route::get('/posts/{id}/comments', [InteractionController::class, 'getComments']);
    Route::post('/posts/{id}/comments', [InteractionController::class, 'addComment']);
    Route::put('/comments/{id}', [InteractionController::class, 'updateComment']);
    Route::delete('/comments/{id}', [InteractionController::class, 'deleteComment']);

    // Interactions - Shares, Saves, Follows
    Route::post('/posts/{id}/share', [InteractionController::class, 'sharePost']);
    Route::post('/posts/{id}/save', [InteractionController::class, 'savePost']);
    Route::delete('/posts/{id}/save', [InteractionController::class, 'unsavePost']);
    Route::post('/users/{id}/follow', [InteractionController::class, 'followUser']);
    Route::delete('/users/{id}/follow', [InteractionController::class, 'unfollowUser']);
    Route::get('/users/{id}/followers', [InteractionController::class, 'followers']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    });
});



// Admin Chat Moderation Routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/chat/admin')->group(function () {
    Route::prefix('reports')->group(function () {
        // Get pending reports
        Route::get('/pending', [ModerationController::class, 'getPendingReports']);
        
        // Get all reports
        Route::get('/', [ModerationController::class, 'getAllReports']);
        
        // Review report
        Route::post('/{id}/review', [ModerationController::class, 'reviewReport']);
    });
});


// Admin Feed Routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/feed/admin')->group(function () {
    // Post moderation
    Route::post('/posts/{id}/pin', [AdminFeedController::class, 'pinPost']);
    Route::post('/posts/{id}/unpin', [AdminFeedController::class, 'unpinPost']);
    Route::post('/posts/{id}/feature', [AdminFeedController::class, 'featurePost']);
    Route::post('/posts/{id}/unfeature', [AdminFeedController::class, 'unfeaturePost']);
    Route::post('/posts/{id}/hide', [AdminFeedController::class, 'hidePost']);
    Route::post('/posts/{id}/publish', [AdminFeedController::class, 'publishPost']);
    Route::delete('/posts/{id}', [AdminFeedController::class, 'deletePost']);

    // Comment moderation
    Route::post('/comments/{id}/hide', [AdminFeedController::class, 'hideComment']);
    Route::post('/comments/{id}/remove', [AdminFeedController::class, 'removeComment']);

    // Reports
    Route::get('/reports', [AdminFeedController::class, 'getReports']);
    Route::post('/reports/{id}/resolve', [AdminFeedController::class, 'resolveReport']);
    Route::post('/reports/{id}/dismiss', [AdminFeedController::class, 'dismissReport']);

    // Categories
    Route::post('/categories', [AdminFeedController::class, 'createCategory']);
    Route::put('/categories/{id}', [AdminFeedController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [AdminFeedController::class, 'deleteCategory']);
    // Statistics
    Route::get('/statistics', [AdminFeedController::class, 'statistics']);
});
// ============================================
// ADVERTISER-FACING ADS ROUTES
// ============================================
Route::middleware(['auth:sanctum'])->prefix('v1/ads')->group(function () {
    Route::get('/advertiser', [AdvertiserController::class, 'show']);
    Route::post('/advertiser/apply', [AdvertiserController::class, 'apply']);

    Route::middleware('advertiser.approved')->group(function () {
        Route::get('/advertisements', [AdvertisementController::class, 'index']);
        Route::post('/advertisements', [AdvertisementController::class, 'store']);
        Route::get('/advertisements/{advertisement}', [AdvertisementController::class, 'show']);
        Route::put('/advertisements/{advertisement}', [AdvertisementController::class, 'update']);
        Route::delete('/advertisements/{advertisement}', [AdvertisementController::class, 'destroy']);

        Route::put('/advertisements/{advertisement}/targeting', [AdvertisementController::class, 'updateTargeting']);
        Route::put('/advertisements/{advertisement}/placements', [AdvertisementController::class, 'updatePlacements']);
        Route::put('/advertisements/{advertisement}/campaign', [AdvertisementController::class, 'updateCampaign']);
        Route::get('/advertisements/{advertisement}/preview', [AdvertisementController::class, 'preview']);
        Route::get('/advertisements/{advertisement}/summary', [AdvertisementController::class, 'summary']);

        Route::post('/advertisements/{advertisement}/media', [AdMediaController::class, 'store']);
        Route::delete('/advertisements/{advertisement}/media/{mediaId}', [AdMediaController::class, 'destroy']);

        Route::post('/advertisements/{advertisement}/payment', [AdPaymentController::class, 'store']);

        Route::post('/advertisements/{advertisement}/pause', [AdvertisementController::class, 'pause']);
        Route::post('/advertisements/{advertisement}/resume', [AdvertisementController::class, 'resume']);
        Route::post('/advertisements/{advertisement}/cancel', [AdvertisementController::class, 'cancel']);
        Route::post('/advertisements/{advertisement}/duplicate', [AdvertisementController::class, 'duplicate']);

        Route::get('/wallet', [AdWalletController::class, 'show']);
        Route::get('/wallet/transactions', [AdWalletController::class, 'transactions']);
        Route::post('/wallet/fund', [AdWalletController::class, 'fund']);

        Route::get('/dashboard', [AdReportController::class, 'dashboard']);
        Route::get('/campaigns/{campaignId}/report', [AdReportController::class, 'campaignReport']);
    });
});

// Public tracking endpoints (throttled to reduce abuse)
Route::middleware('throttle:120,1')->prefix('v1/ads')->group(function () {
    Route::post('/campaigns/{campaign}/impression', [AdTrackingController::class, 'impression']);
    Route::post('/campaigns/{campaign}/click', [AdTrackingController::class, 'click']);
});

Route::middleware(['auth:sanctum'])->prefix('v1/ads')->group(function () {
    Route::post('/campaigns/{campaign}/conversion', [AdTrackingController::class, 'conversion']);
});

// ============================================
// ADMIN ADS MANAGEMENT ROUTES
// ============================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/ads')->group(function () {
    Route::get('/advertisements', [AdminAdvertisementController::class, 'index']);
    Route::get('/advertisements/{advertisement}', [AdminAdvertisementController::class, 'show']);
    Route::post('/advertisements/{advertisement}/approve', [AdminAdvertisementController::class, 'approve']);
    Route::post('/advertisements/{advertisement}/reject', [AdminAdvertisementController::class, 'reject']);
    Route::post('/advertisements/{advertisement}/request-changes', [AdminAdvertisementController::class, 'requestChanges']);
    Route::post('/advertisements/{advertisement}/pause', [AdminAdvertisementController::class, 'pause']);
    Route::post('/advertisements/{advertisement}/resume', [AdminAdvertisementController::class, 'resume']);
    Route::post('/advertisements/{advertisement}/suspend', [AdminAdvertisementController::class, 'suspend']);
    Route::delete('/advertisements/{advertisement}', [AdminAdvertisementController::class, 'destroy']);

    Route::get('/advertisers', [AdminAdvertiserController::class, 'index']);
    Route::post('/advertisers/{advertiser}/approve', [AdminAdvertiserController::class, 'approve']);
    Route::post('/advertisers/{advertiser}/reject', [AdminAdvertiserController::class, 'reject']);
    Route::post('/advertisers/{advertiser}/suspend', [AdminAdvertiserController::class, 'suspend']);

    Route::get('/packages', [AdminAdPackageController::class, 'index']);
    Route::post('/packages', [AdminAdPackageController::class, 'store']);
    Route::put('/packages/{adPackage}', [AdminAdPackageController::class, 'update']);
    Route::delete('/packages/{adPackage}', [AdminAdPackageController::class, 'destroy']);

    Route::get('/pricing', [AdminAdPricingController::class, 'index']);
    Route::post('/pricing', [AdminAdPricingController::class, 'store']);
    Route::put('/pricing/{adPricingModel}', [AdminAdPricingController::class, 'update']);
    Route::delete('/pricing/{adPricingModel}', [AdminAdPricingController::class, 'destroy']);

    Route::get('/placements', [AdminAdPlacementController::class, 'index']);
    Route::post('/placements', [AdminAdPlacementController::class, 'store']);
    Route::put('/placements/{adPlacement}', [AdminAdPlacementController::class, 'update']);

    Route::get('/settings', [AdminAdSettingController::class, 'index']);
    Route::put('/settings', [AdminAdSettingController::class, 'update']);

    Route::get('/revenue', [AdminAdRevenueController::class, 'revenue']);
    Route::get('/analytics', [AdminAdRevenueController::class, 'analytics']);

    Route::post('/payments/{payment}/refund', [AdminAdRefundController::class, 'store']);
});
// In-App Notification Feed routes
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/',           [NotificationFeedController::class, 'index']);
    Route::post('/read-all',  [NotificationFeedController::class, 'markAllAsRead']);
    Route::post('/{id}/read', [NotificationFeedController::class, 'markAsRead']);
});


// ============================================
// MARKET AGENT MODULE — ROUTE DEFINITIONS
// ============================================

// Agent authentication routes for the mobile app
Route::prefix('marketAgent/auth')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register', [AgentAuthController::class, 'register']);
        Route::post('/login', [AgentAuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'role:agent'])->group(function () {
        Route::post('/logout', [AgentAuthController::class, 'logout']);
        Route::get('/me', [AgentAuthController::class, 'me']);
    });
});

// Agent-facing (mobile app) routes
Route::middleware(['auth:sanctum', 'role:agent'])->prefix('marketAgent')->group(function () {

    Route::get('/profile', [AgentProfileController::class, 'show']);

    Route::get('/kpi/daily', [AgentKpiController::class, 'daily']);
    Route::get('/kpi/weekly', [AgentKpiController::class, 'weekly']);

    Route::get('/earnings', [AgentEarningController::class, 'show']);

    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::post('/', [LeadController::class, 'store']);
        Route::get('/{id}', [LeadController::class, 'show']);
        Route::put('/{id}', [LeadController::class, 'update']);
        Route::put('/{id}/classify', [LeadController::class, 'classify']);
        Route::post('/{id}/follow-up', [LeadController::class, 'logFollowUp']);
        Route::post('/{id}/link-customer', [LeadController::class, 'linkCustomer']);
    });

    Route::prefix('activation')->group(function () {
        Route::post('/initiate', [ActivationPaymentController::class, 'initiate']);
        Route::get('/{reference}/status', [ActivationPaymentController::class, 'status']);
    });

    Route::prefix('reports')->group(function () {
        Route::post('/daily', [AgentDailyReportController::class, 'store']);
        Route::get('/daily', [AgentDailyReportController::class, 'index']);
        Route::get('/daily/{id}', [AgentDailyReportController::class, 'show']);
    });
});

// Admin oversight routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/agents')->group(function () {
    Route::get('/dashboard', [AgentAdminController::class, 'dashboard']);
    Route::get('/', [AgentAdminController::class, 'index']);
    Route::post('/', [AgentAdminController::class, 'store']);
    Route::get('/{id}', [AgentAdminController::class, 'show']);
    Route::get('/{id}/performance', [AgentAdminController::class, 'performance']);
    Route::get('/{id}/reports', [AgentAdminController::class, 'reports']);
    Route::put('/{id}/territory', [AgentAdminController::class, 'assignTerritory']);
    Route::put('/{id}/status', [AgentAdminController::class, 'updateStatus']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/leads')->group(function () {
    Route::get('/', [LeadAdminController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/agent-earnings')->group(function () {
    Route::get('/', [AgentEarningAdminController::class, 'index']);
    Route::post('/{id}/approve', [AgentEarningAdminController::class, 'approve']);
    Route::post('/{id}/mark-paid', [AgentEarningAdminController::class, 'markPaid']);
});
