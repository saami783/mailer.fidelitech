<?php

namespace App\Enum;

enum LogActionEnum
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const IMPORT = 'import';

    public const ALL = [
        self::CREATE,
        self::UPDATE,
        self::DELETE,
        self::IMPORT,
    ];
}
