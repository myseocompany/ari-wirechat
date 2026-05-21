<?php

namespace App\Enums;

enum CustomerMaker: int
{
    case Project = 0;
    case MakesEmpanadas = 1;
    case Other = 2;

    public static function fromDatabase(mixed $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom((int) $value);
    }

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Proyecto',
            self::MakesEmpanadas => 'Hace empanadas',
            self::Other => 'Otros',
        };
    }
}
