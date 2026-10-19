<?php

namespace App\Modules\Shared\Presentation\Console;

use App\Modules\Shared\Infrastructure\Sms\BalanceWatch;
use App\Modules\Shared\Infrastructure\Sms\SmsProviderRegistry;
use Illuminate\Console\Command;

class WatchSmsBalances extends Command
{
    protected $signature = 'sms:watch-balances';

    protected $description = 'Refresh configured SMS balances and alert when a threshold is reached';

    public function handle(SmsProviderRegistry $registry, BalanceWatch $watch): int
    {
        foreach ($registry->records() as $config) {
            if (! $config->isConfigured() || ! $config->supportsBalance()) {
                continue;
            }

            $balance = $registry->makeFor($config)->balance();
            $config->forceFill([
                'balance_snapshot' => $balance->toArray(),
                'balance_checked_at' => now(),
            ])->save();
            $watch->notifyIfNeeded($config->fresh());
        }

        return self::SUCCESS;
    }
}
