<?php

namespace App\Enums;

enum AnimalSpeciesEnum: string
{
    case SWINE = 'suinos';
    case GOAT = 'caprinos';
    case CATTLE = 'bovinos';

    public function label(): string
    {
        return match($this) {
            self::SWINE => 'Suínos',
            self::GOAT => 'Caprinos',
            self::CATTLE => 'Bovinos',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label()
            ],
            self::cases()
        );
    }
}
