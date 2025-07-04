<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum LocaleEnum
{
    public const FR = 'fr';
    public const EN = 'en';

    public static function getAvailableLocales(): Collection
    {
        return collect([self::FR => 'french', self::EN => 'english']);
    }

    public static function getDefaultLocale(): string
    {
        return config('app.locale');//en
    }
}
