<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class LogMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user, $time;

    /**
     * Create a new job instance.
     */
    public function __construct($user,$time)
    {
        $this->user = $user;
        $this->time = $time;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // render the message via View
        $viewContent = View::make('notification_message',['user' => $this->user, 'scheduledTime' => $this->time])->render();

        // Log the content to the laravel.log file
        Log::info($viewContent);
    }
}
