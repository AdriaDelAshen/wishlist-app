<?php

namespace App\Enums;

enum LocaleEnum
{
    public const FR = 'fr';
    public const EN = 'en';

    public static function getAvailableLocales(): array
    {
        return [self::FR => 'french', self::EN => 'english'];
    }

    public static function getDefaultLocale(): string
    {
        return config('app.locale');//en
    }
}
