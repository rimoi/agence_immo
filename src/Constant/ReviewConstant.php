<?php

namespace App\Constant;

class ReviewConstant
{
    public const BAD = 1;
    public const MEDIUM = 2;
    public const GOOD = 3;

    public const VERY_GOOD = 4;
    public const EXCELLENT = 5;

    public const MAP = [
        'Excellent' => self::EXCELLENT,
        'Très satisfaisant' => self::VERY_GOOD,
        'Bien' => self::GOOD,
        'Moyen' => self::MEDIUM,
        'Déçu' => self::BAD,
    ];
}
