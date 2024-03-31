<?php

namespace App\Constant;

class UserConstant
{
    public const ADMIN     = "Administrateur";
    public const OWNER = "Propriétaire";
    public const CLIENT      = "Locataire";

    public const ROLE_ADMIN     = "ROLE_ADMIN";
    public const ROLE_OWNER = "ROLE_OWNER";
    public const ROLE_CLIENT      = "ROLE_CLIENT";
    public const ROLE_USER      = "ROLE_USER";

    private static $MAP = [
        self::ADMIN   => self::ADMIN,
        self::OWNER => self::OWNER,
        self::CLIENT    => self::CLIENT
    ];

    private static $MAP_STRING = [
        self::ADMIN   => self::ROLE_ADMIN,
        self::OWNER => self::ROLE_OWNER,
        self::CLIENT    => self::ROLE_CLIENT
    ];

    public static function all(): array
    {
        return self::$MAP;
    }

    public static function asString($role): ?string
    {
        return self::$MAP_STRING[$role] ?? null;
    }

    public static function asStringInverse($role): ?string
    {
        $flipRole = array_flip(self::$MAP_STRING);

        return $flipRole[$role] ?? null;
    }
}
