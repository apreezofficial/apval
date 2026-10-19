<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Exceptions\Domain\InvalidOrderStateException;
use App\Models\Inspection;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    public function assignInspector(string $orderItemId, string $inspectorId): Inspection
    {
        $inspection = Inspection::where('order_item_id', $orderItemId)->firstOrFail();

        if ($inspection->status !== 'pending') {
            throw new InvalidOrderStateException('Inspection is not in pending status.');
        }

        $inspection->update([
            'inspector_id' => $inspectorId,
            'status' => 'assigned',
        ]);

        return $inspection;
    }

    public function submitInspection(string $inspectorId, string $inspectionId, array $evidenceUrls, string $notes, bool $passed): Inspection
    {
        return DB::transaction(function () use ($inspectorId, $inspectionId, $evidenceUrls, $notes, $passed) {
            $inspection = Inspection::where('id', $inspectionId)->where('inspector_id', $inspectorId)->firstOrFail();

            if ($inspection->status !== 'assigned') {
                throw new InvalidOrderStateException('Inspection must be assigned to submit evidence.');
            }

            $newStatus = $passed ? 'passed' : 'failed';

            $inspection->update([
                'status' => $newStatus,
                'evidence' => $evidenceUrls,
                'notes' => $notes,
            ]);

            // Update underlying order item state
            $orderItem = $inspection->orderItem;
            if ($passed) {
                // Moving towards Dispatch state
                $orderItem->update([
                    'vendor_status' => 'inspected_approved',
                ]);
            } else {
                $orderItem->update([
                    'vendor_status' => 'inspected_rejected', // Flagging for dispute/vendor remediation
                ]);
            }

            return $inspection;
        });
    }
}
