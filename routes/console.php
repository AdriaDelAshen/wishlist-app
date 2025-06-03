<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

//Need to add this in crontab on the server in production.
// * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1
Schedule::command('app:notify-users-of-upcoming-birthdays')->daily();
Schedule::command('app:clean-up-old-unaccepted-group-invitations')->daily();
