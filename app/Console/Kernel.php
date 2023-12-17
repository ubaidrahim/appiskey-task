<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use App\Jobs\LogMessageJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $notifications = UserNotification::all();
        // $schedule->command('inspire')->hourly();
        foreach ($notifications as $notification) {
            $timezone = $notification->user->timezone;
            $time = $notification->scheduled_at;
            $user = $notification->user;
            switch ($notification->frequency) {
                case 'daily':
                    $schedule->job(new LogMessageJob($user, $time))
                    ->dailyAt($time)
                    ->timezone($timezone);
                    break;
                case 'weekly':
                    $schedule->job(new LogMessageJob($user, $time))
                    ->weeklyOn(1, $time)
                    ->timezone($timezone);
                    break;
                case 'monthly':
                    $schedule->job(new LogMessageJob($user, $time))
                    ->monthlyOn(1, $time)
                    ->timezone($timezone);
                    break;
                
                default:
                    # code...
                    break;
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
