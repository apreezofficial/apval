<?php

declare(strict_types=1);

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\DispatchJob;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LogisticsReportService
{
    /**
     * Get all metrics required for the Section 8 Admin Dashboard.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'order_status_breakdown'  => $this->getOrderStatusBreakdown(),
            'vehicle_type_usage'      => $this->getVehicleTypeUsage(),
            'cold_room_usage'         => $this->getColdRoomUsage(),
            'exceptions'              => $this->getFailedAndDelayed(),
            'areaconnect_performance' => $this->getAreaConnectPerformance(),
            'financial_summary'       => $this->getFinancialSummary(),
        ];
    }

    /**
     * 1. Order vs delivery status
     */
    private function getOrderStatusBreakdown(): array
    {
        return Order::select('logistics_status', DB::raw('count(*) as count'))
            ->whereNotNull('logistics_status')
            ->groupBy('logistics_status')
            ->pluck('count', 'logistics_status')
            ->toArray();
    }

    /**
     * 2. Vehicle type usage
     */
    private function getVehicleTypeUsage(): array
    {
        return Order::select('transport_type', DB::raw('count(*) as count'))
            ->whereNotNull('transport_type')
            ->groupBy('transport_type')
            ->pluck('count', 'transport_type')
            ->toArray();
    }

    /**
     * 3. Cold room usage (PDF Section 8 & 4.B)
     * Calculates total jobs that entered cold room and total hours spent.
     */
    private function getColdRoomUsage(): array
    {
        $stats = DB::table('dispatch_jobs')
            ->selectRaw('
                COUNT(*) as total_cold_chain_jobs,
                COALESCE(SUM(TIMESTAMPDIFF(MINUTE, cold_room_entry_at, cold_room_exit_at)), 0) / 60.0 as total_hours
            ')
            ->whereNotNull('cold_room_entry_at')
            ->whereNotNull('cold_room_exit_at')
            ->first();

        return [
            'total_jobs'   => (int) ($stats->total_cold_chain_jobs ?? 0),
            'total_hours'  => round((float) ($stats->total_hours ?? 0), 2),
            'total_fees'   => (float) Order::where('cold_chain_required', true)->sum('cold_room_fee'),
        ];
    }

    /**
     * 4. Failed or delayed deliveries (PDF Section 8 & 9)
     */
    private function getFailedAndDelayed(): array
    {
        $failedCount = Order::whereIn('logistics_status', ['failed', 'delivery_failed', 'delivery_returned', 'cancelled'])->count();

        // Deliveries that arrived LATER than estimated
        $deliveredLateCount = DispatchJob::whereNotNull('estimated_delivery_at')
            ->whereNotNull('delivered_at')
            ->whereColumn('delivered_at', '>', 'estimated_delivery_at')
            ->count();

        // Deliveries currently in transit but PAST their estimated time (Active Delays)
        $currentlyDelayedCount = DispatchJob::whereNull('delivered_at')
            ->where('estimated_delivery_at', '<', Carbon::now())
            ->whereIn('status', ['in_transit', 'out_for_delivery', 'picked_up'])
            ->count();

        return [
            'failed_orders'      => $failedCount,
            'delivered_late'     => $deliveredLateCount,
            'currently_delayed'  => $currentlyDelayedCount,
        ];
    }

    /**
     * 5. AreaConnect performance metrics
     */
    private function getAreaConnectPerformance(): array
    {
        $totalDelivered = Order::where('logistics_status', 'delivered')->count();
        $totalFailed = Order::whereIn('logistics_status', ['failed', 'delivery_failed', 'cancelled'])->count();
        $totalProcessed = $totalDelivered + $totalFailed;

        $successRate = $totalProcessed > 0 ? round(($totalDelivered / $totalProcessed) * 100, 2) : 0;

        return [
            'total_delivered' => $totalDelivered,
            'total_failed'    => $totalFailed,
            'success_rate'    => $successRate, // Percentage
        ];
    }

    /**
     * Financial Summary (PDF Section 7 & 8)
     */
    private function getFinancialSummary(): array
    {
        return [
            'total_logistics_fees'   => (float) Order::sum('logistics_fee'),
            'total_cold_room_fees'   => (float) Order::sum('cold_room_fee'),
            'total_platform_margin'  => (float) Order::sum('platform_margin'),
            'total_areaconnect_payable' => (float) Order::sum('areaconnect_payable'),
        ];
    }
}