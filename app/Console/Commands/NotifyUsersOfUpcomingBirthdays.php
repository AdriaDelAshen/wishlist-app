<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\UpcomingBirthdayNotification;
use Illuminate\Console\Command;

class NotifyUsersOfUpcomingBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-users-of-upcoming-birthdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification two weeks in advance to users of the upcoming birthdays.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = now(config('app.timezone'))->addDays(14);
        $monthDay = $targetDate->format('m-d');

        $users = User::query()
            ->whereNotNull('birthday_date')
            ->whereRaw("DATE_FORMAT(birthday_date, '%m-%d') = ?", [$monthDay])
            ->get()
            ->each(function ($user) {
                $user->notify(new UpcomingBirthdayNotification());
            });

        $this->info("Notified {$users->count()} user(s) with birthdays on {$targetDate}.");
    }
}
