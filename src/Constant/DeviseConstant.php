<?php

namespace App\Constant;

class DeviseConstant
{
    public const DIRHAM_LABEL = 'DIRHAM 🇲🇦';
    public const OUGIYA_LABEL = 'OUGIYA 🇲🇷';
    public const EURO_LABEL = 'EURO 🇪🇺';
    public const DOLLAR_STERLIN_LABEL = 'LIVRE STERLING 🇬🇧';
    public const DOLLAR_LABEL = 'DOLLAR 🇺🇸';


    public const DIRHAM = 'DIRHAM';
    public const OUGIYA = 'OUGIYA';
    public const EURO = 'EURO';
    public const DOLLAR_STERLIN = 'LIVRE STERLING';
    public const DOLLAR = 'DOLLAR';

    public const MAP = [
        self::DIRHAM_LABEL => self::DIRHAM,
        self::OUGIYA_LABEL => self::OUGIYA,
        self::EURO_LABEL => self::EURO,
        self::DOLLAR_STERLIN_LABEL => self::DOLLAR_STERLIN,
        self::DOLLAR_LABEL => self::DOLLAR,
    ];

    public const REVERSE_MAP = [
        self::DIRHAM => '🇲🇦',
        self::OUGIYA => '🇲🇷',
        self::EURO => '🇪🇺',
        self::DOLLAR_STERLIN => '🇬🇧',
        self::DOLLAR => '🇺🇸',
    ];
}
