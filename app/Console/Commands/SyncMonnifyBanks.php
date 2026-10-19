<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Services\Kyc\MonnifyClient;
use Illuminate\Console\Command;

class SyncMonnifyBanks extends Command
{
    protected $signature = 'monnify:banks-sync';
    protected $description = 'Sync bank list from Monnify';

    public function handle(MonnifyClient $monnify)
    {
        $this->info('Fetching banks from Monnify...');

        [$status, $json] = $monnify->get('/api/v1/banks');

        if (!$json['requestSuccessful']) {
            $this->error('Failed to fetch banks');
            return;
        }

        foreach ($json['responseBody'] as $bank) {

            Bank::updateOrCreate(
                [
                    'provider' => 'monnify',
                    'code' => $bank['code']
                ],
                [
                    'name' => $bank['name'],
                    'active' => true
                ]
            );

        }

        $this->info('Banks synced successfully.');

        return Command::SUCCESS;
    }
}
