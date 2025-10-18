<?php

namespace App\Enums;

enum LivestockPurposeEnum: string
{
    case BREEDING = 'reproducao';
    case MEAT = 'corte';
    case MILK = 'leite';
    case MIXED = 'misto';

    public function label(): string
    {
        return match($this) {
            self::BREEDING => 'Reprodução',
            self::MEAT => 'Corte',
            self::MILK => 'Leite',
            self::MIXED => 'Misto',
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
