<?php

namespace Database\Factories\Agent;

use App\Models\Agent\Lead;
use App\Models\Agent\LeadFollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\LeadFollowUp>
 */
class LeadFollowUpFactory extends Factory
{
    protected $model = LeadFollowUp::class;

    public function definition(): array
    {
        $lead = Lead::factory()->create();

        return [
            'lead_id' => $lead->id,
            'agent_id' => $lead->agent_id,
            'classification_before' => 'new',
            'classification_after' => fake()->randomElement(['hot', 'warm', 'cold']),
            'channel' => fake()->randomElement(['call', 'visit', 'sms', 'whatsapp']),
            'outcome' => fake()->randomElement(['interested', 'not_interested', 'no_answer']),
            'note' => fake()->sentence(),
            'followed_up_at' => now(),
        ];
    }
}