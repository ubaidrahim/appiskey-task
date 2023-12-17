<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use App\Jobs\LogMessageJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserNotification;

use Tests\TestCase;

class TaskTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase; // This trait resets the database after each test

    public function test_example(): void
    {
        // Test the Database Seeder
        $this->seed(DatabaseSeeder::class);

        // Count number of Users and Notifications inserted by Seeders
        $userCount = DB::table('users')->count();
        $notificationsCount = DB::table('user_notifications')->count();

        // Ensure the seeder inserted 20 Users and 20 notifications as specified
        $this->assertEquals(20, $userCount);
        $this->assertEquals(20, $notificationsCount);

        // Fetch sample data from inserted record
        $sampleUser = DB::table('users')->first();
        $sampleNotification = UserNotification::first();

        // Check if required columns from both tables are not null
        $this->assertNotNull($sampleUser->name);
        $this->assertNotNull($sampleUser->email);
        $this->assertNotNull($sampleUser->timezone);
        $this->assertNotNull($sampleUser->created_at);
        $this->assertNotNull($sampleUser->updated_at);
        $this->assertNotNull($sampleNotification->user_id);
        $this->assertNotNull($sampleNotification->scheduled_at);
        $this->assertNotNull($sampleNotification->frequency);
        $this->assertNotNull($sampleNotification->created_at);
        $this->assertNotNull($sampleNotification->updated_at);

        // Dispatch the Log Message job
        Bus::dispatch(new LogMessageJob($sampleNotification->user, $sampleNotification->scheduled_at));

        // Check the laravel.log for message
        $logContent = file_get_contents(storage_path('logs/laravel.log'));
        $this->assertStringContainsString('Hello '.$sampleNotification->user->name, $logContent);
        $this->assertStringContainsString('Your email address is: '.$sampleNotification->user->email, $logContent);

        // Run the scheduler command to trigger the task schedular
        Artisan::call('schedule:run');
        $logContent = file_get_contents(storage_path('logs/laravel.log'));

        // Check if laravel.log is updated based on current time with matching user's notifications and their timezone
        $notifications = UserNotification::get()->filter(function ($notification) {
                    // Get the user's timezone
                    $userTimezone = $notification->user->timezone;

                    // Convert the scheduled_at time to the user's timezone
                    $scheduledTimeInUserTimezone = Carbon::parse($notification->scheduled_at)
                        ->timezone($userTimezone)
                        ->format('H:i');

                    // Get the current time in the user's timezone
                    $currentTimeInUserTimezone = Carbon::now($userTimezone)->format('H:i');

                    // Compare the times (assuming you want to check if the current time matches the scheduled time)
                    return $scheduledTimeInUserTimezone === $currentTimeInUserTimezone;
                });
            foreach ($notifications as $thisnotification) {
                $this->assertStringContainsString('Hello '.$thisnotification->user->name, $logContent);
                $this->assertStringContainsString('Your email address is: '.$thisnotification->user->email, $logContent);
            }
    }
}
