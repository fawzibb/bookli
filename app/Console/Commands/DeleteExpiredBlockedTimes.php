<?php

namespace App\Console\Commands;

use App\Models\BlockedTime;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredBlockedTimes extends Command
{
    protected $signature = 'blocked-times:delete-expired';

    protected $description = 'Delete expired specific blocked times';

    public function handle()
    {
        $now = Carbon::now('Asia/Beirut');

        BlockedTime::whereNotNull('blocked_date')
            ->where(function ($query) use ($now) {
                $query->whereDate('blocked_date', '<', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('blocked_date', $now->toDateString())
                          ->whereTime('end_time', '<=', $now->format('H:i:s'));
                    });
            })
            ->delete();

        return Command::SUCCESS;
    }
}