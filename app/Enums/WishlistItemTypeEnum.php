<?php

namespace App\Enums;


use Illuminate\Support\Collection;

enum WishlistItemTypeEnum: string
{
    case GROUP_GIFT = 'group_gift';
    case ONE_PERSON_GIFT = 'one_person_gift';

    public static function getAvailableTypes(): Collection
    {
        return collect([
            self::GROUP_GIFT->value => 'group_gift',
            self::ONE_PERSON_GIFT->value => 'one_person_gift'
        ]);
    }
}
