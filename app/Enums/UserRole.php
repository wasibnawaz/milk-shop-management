<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::Cashier => 'Cashier',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Full access, including user management and permanent deletion.',
            self::Manager => 'Manages sales, products and dealers. Cannot manage users.',
            self::Cashier => 'Records and edits their own sales. Cannot delete or manage the catalogue.',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-100 text-violet-800 ring-violet-600/20 dark:bg-violet-500/15 dark:text-violet-300 dark:ring-violet-400/30',
            self::Manager => 'bg-brand-100 text-brand-800 ring-brand-600/20 dark:bg-brand-500/15 dark:text-brand-300 dark:ring-brand-400/30',
            self::Cashier => 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-700 dark:text-slate-300 dark:ring-slate-500/30',
        };
    }

    /** Roles that may manage the product and dealer catalogue. */
    public function managesCatalogue(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    /** Roles that may delete records rather than only create and edit. */
    public function canDelete(): bool
    {
        return in_array($this, [self::Admin, self::Manager], true);
    }

    /** @return array<string, string> value => label, for <select> options. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
