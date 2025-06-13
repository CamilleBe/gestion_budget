<?php

namespace App\Enum;

enum CategoryType: string
{
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';

    public function getLabel(): string
    {
        return match($this) {
            self::INCOME => 'Revenu',
            self::EXPENSE => 'Dépense',
        };
    }

    public static function getChoices(): array
    {
        return [
            'Revenu' => self::INCOME->value,
            'Dépense' => self::EXPENSE->value,
        ];
    }
} 