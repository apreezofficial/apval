<?php
declare(strict_types=1);
namespace App\Services\AI;
use Carbon\Carbon;

class FarmingCalendarTool {
    // Standard crop cycles (weeks from planting to harvest)
    private const CROP_CYCLES = [
        'maize' => ['total_weeks' => 16, 'stages' => [
            ['weeks' => [1], 'activity' => 'Land preparation, ridging, and basal fertilizer application'],
            ['weeks' => [2, 3], 'activity' => 'Planting and germination monitoring'],
            ['weeks' => [4, 5], 'activity' => 'Weed management and first top-dressing (urea)'],
            ['weeks' => [6, 8], 'activity' => 'Vegetative growth — pest scouting and second fertilizer application'],
            ['weeks' => [9, 11], 'activity' => 'Tasseling and silking — monitor for stem borers'],
            ['weeks' => [12, 14], 'activity' => 'Grain filling — reduce irrigation as maturity approaches'],
            ['weeks' => [15, 16], 'activity' => 'Harvest, dry, and store'],
        ]],
        'tomato' => ['total_weeks' => 14, 'stages' => [
            ['weeks' => [1], 'activity' => 'Nursery preparation and seed sowing'],
            ['weeks' => [2, 3], 'activity' => 'Seedling care and hardening'],
            ['weeks' => [4], 'activity' => 'Transplanting to main field'],
            ['weeks' => [5, 7], 'activity' => 'Staking, weeding, and fertilizer application'],
            ['weeks' => [8, 10], 'activity' => 'Flowering and fruit set — pest/disease monitoring'],
            ['weeks' => [11, 13], 'activity' => 'Fruit development and harvesting begins'],
            ['weeks' => [14], 'activity' => 'Final harvest and field cleanup'],
        ]],
        'rice' => ['total_weeks' => 18, 'stages' => [
            ['weeks' => [1, 2], 'activity' => 'Land preparation and puddling'],
            ['weeks' => [3], 'activity' => 'Nursery sowing or direct seeding'],
            ['weeks' => [4, 6], 'activity' => 'Transplanting and early weed management'],
            ['weeks' => [7, 10], 'activity' => 'Tillering stage — water management and fertilizer'],
            ['weeks' => [11, 14], 'activity' => 'Panicle initiation and flowering'],
            ['weeks' => [15, 17], 'activity' => 'Grain filling — drain fields 2 weeks before harvest'],
            ['weeks' => [18], 'activity' => 'Harvest, thresh, and dry'],
        ]],
    ];

    public function generateCalendar(string $crop, string $plantingDate): array {
        $crop = strtolower($crop);
        $cycle = self::CROP_CYCLES[$crop] ?? self::CROP_CYCLES['maize'];
        $start = Carbon::parse($plantingDate);

        $calendar = [];
        foreach ($cycle['stages'] as $stage) {
            foreach ($stage['weeks'] as $weekNum) {
                $weekStart = $start->copy()->addWeeks($weekNum - 1);
                $weekEnd = $weekStart->copy()->addDays(6);
                $calendar[] = [
                    'week' => $weekNum,
                    'period' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y'),
                    'activity' => $stage['activity'],
                ];
            }
        }

        return [
            'crop' => ucfirst($crop),
            'planting_date' => $start->toDateString(),
            'expected_harvest' => $start->copy()->addWeeks($cycle['total_weeks'])->toDateString(),
            'total_weeks' => $cycle['total_weeks'],
            'calendar' => $calendar,
        ];
    }
}