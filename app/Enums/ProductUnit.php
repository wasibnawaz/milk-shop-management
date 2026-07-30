<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Litre = 'litre';
    case Kilogram = 'kg';
    case Piece = 'piece';
    case Packet = 'packet';
    case Dozen = 'dozen';

    public function label(): string
    {
        return match ($this) {
            self::Litre => 'Litre',
            self::Kilogram => 'Kilogram',
            self::Piece => 'Piece',
            self::Packet => 'Packet',
            self::Dozen => 'Dozen',
        };
    }

    /** Short form used in tables and next to quantities, e.g. "12.5 L". */
    public function abbreviation(): string
    {
        return match ($this) {
            self::Litre => 'L',
            self::Kilogram => 'kg',
            self::Piece => 'pc',
            self::Packet => 'pkt',
            self::Dozen => 'dz',
        };
    }

    /**
     * Whether this unit is sold in fractions. Litres and kilograms are; you
     * cannot sell a third of a packet, so the form steps those by 1.
     */
    public function allowsFractions(): bool
    {
        return in_array($this, [self::Litre, self::Kilogram], true);
    }

    /** @return array<string, string> value => label, for <select> options. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
