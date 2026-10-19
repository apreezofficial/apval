<?php

declare(strict_types=1);

namespace App\Services\Logistics;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementService
{
    public function calculateAndRecordSettlement(Order $order): array
    {
        if ($order->areaconnect_payable > 0 || $order->logistics_fee > 0) {
            return [
                'transport_fee' => (float) $order->logistics_fee,
                'cold_room_fee' => (float) $order->cold_room_fee,
            ];
        }

        return DB::transaction(function () use ($order) {
            $dispatchJob = $order->items()->with('dispatchJob')->first()?->dispatchJob;

            $transportFee = $this->calculateTransportFee(
                $order->transport_type ?? 'bike',
                (float) ($order->estimated_weight_kg ?? 1.0)
            );

            $coldRoomFee = 0.0;
            if ($order->cold_chain_required && $dispatchJob) {
                $entryTime = $dispatchJob->cold_room_entry_at;
                $exitTime = $dispatchJob->cold_room_exit_at;

                if ($entryTime && $exitTime) {
                    $durationHours = max(1, $entryTime->diffInHours($exitTime));
                    $hourlyRate = (float) config('services.areaconnect.cold_room_hourly_rate', 150.00);
                    $coldRoomFee = $durationHours * $hourlyRate;
                }
            }

            $subtotal = $transportFee + $coldRoomFee;
            $marginPercentage = (float) config('services.agreonpay.logistics_margin', 0.10);
            $platformMargin = $subtotal * $marginPercentage;
            $areaconnectPayable = $subtotal;

            $order->update([
                'logistics_fee'       => $transportFee,
                'cold_room_fee'       => $coldRoomFee,
                'platform_margin'     => $platformMargin,
                'areaconnect_payable' => $areaconnectPayable,
            ]);

            return [
                'transport_fee' => $transportFee,
                'cold_room_fee' => $coldRoomFee,
                'platform_margin' => $platformMargin,
                'areaconnect_payable' => $areaconnectPayable,
            ];
        });
    }

    private function calculateTransportFee(string $type, float $weight): float
    {
        return match ($type) {
            'bike'  => 500.00 + ($weight * 50.00),
            'van'   => 1500.00 + ($weight * 100.00),
            'truck' => 5000.00 + ($weight * 200.00),
            default => 500.00,
        };
    }
}