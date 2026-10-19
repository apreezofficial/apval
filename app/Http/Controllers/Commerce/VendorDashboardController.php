<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorDashboardController
{
    public function overview(): JsonResponse
    {
        $user = Auth::user();
        $vendorId = $user->id;

        $stats = DB::transaction(function () use ($vendorId, $user) {
            // Base query for this vendor's items
            $vendorItems = OrderItem::where('vendor_id', $vendorId);

            // Sales (Total items sold, excluding cancelled)
            $sales = (float) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))
                ->sum('quantity');

            // Revenue (Total from completed orders)
            $revenue = (float) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->where('status', 'completed'))
                ->sum('total_price');

            // Returns (Items in returned status)
            $returns = (float) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->where('status', 'returned'))
                ->sum('quantity');

            // Completed Orders Count
            $completeOrders = (int) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->where('status', 'completed'))
                ->distinct('order_id')
                ->count('order_id');

            // Pending Orders Count
            $pendingOrders = (int) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'processing', 'paid']))
                ->distinct('order_id')
                ->count('order_id');

            // Total Orders Count
            $totalOrders = (int) (clone $vendorItems)
                ->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))
                ->distinct('order_id')
                ->count('order_id');

            // Total Customers
            $totalCustomers = (int) (clone $vendorItems)
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->distinct('orders.user_id')
                ->count('orders.user_id');

            $wallet = $user->wallets()->where('currency', 'NGN')->first();
            $walletBalance = $wallet ? (float) $wallet->balance : 0.0;

            return [
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'sales' => number_format($sales, 2, '.', ''),
                        'revenue' => number_format($revenue, 2, '.', ''),
                        'returns' => number_format($returns, 2, '.', ''),
                        'complete_orders' => (string) $completeOrders,
                        'total_customers' => $totalCustomers,
                        'new_customers' => 0, // Simplified for now
                    ],
                    'totalRevenue' => $revenue,
                    'totalOrders' => $totalOrders,
                    'pendingOrders' => $pendingOrders,
                    'completedOrders' => $completeOrders,
                    'totalSales' => $sales,
                    'totalCustomers' => $totalCustomers,
                    'walletBalance' => $walletBalance,
                ]
            ];
        });

        return response()->json($stats);
    }

    public function transactions(): JsonResponse
    {
        $user = Auth::user();
        $walletId = $user->wallets()->where('currency', '=', 'NGN')->value('id');

        if (!$walletId) {
            return response()->json([
                'status' => 'success',
                'data' => ['transactions' => []]
            ]);
        }

        // Fetch transactions where this vendor is the recipient (sales)
        $transactions = Transaction::where('to_wallet_id', $walletId)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'transactions' => $transactions->map(function ($txn) {
                    return [
                        'transaction_id' => $txn->id,
                        'amount' => $txn->amount,
                        'description' => $txn->description ?? 'Sale payment',
                        'transaction_date' => $txn->created_at->toIso8601String(),
                        'status' => $txn->status ?? 'completed',
                        'transaction_type' => 'credit'
                    ];
                })
            ]
        ]);
    }
}
