<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies a whitelisted sort to a query from request input.
 *
 * The whitelist matters: passing raw request input into orderBy() is an SQL
 * injection vector, so an unrecognised column silently falls back to the
 * default rather than reaching the database.
 */
class TableSort
{
    /**
     * @param  array<string, string|Expression|Closure>  $allowed  public name => column, expression or closure
     */
    public function __construct(
        private array $allowed,
        private string $defaultColumn,
        private string $defaultDirection = 'desc',
    ) {}

    /**
     * @param  array<string, string|Expression|Closure>  $allowed
     */
    public static function make(array $allowed, string $defaultColumn, string $defaultDirection = 'desc'): self
    {
        return new self($allowed, $defaultColumn, $defaultDirection);
    }

    public function column(Request $request): string
    {
        $requested = $request->string('sort')->toString();

        return array_key_exists($requested, $this->allowed) ? $requested : $this->defaultColumn;
    }

    public function direction(Request $request): string
    {
        return $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    public function apply(Builder $query, Request $request): void
    {
        $column = $this->column($request);
        $direction = $this->direction($request);
        $target = $this->allowed[$column];

        if ($target instanceof Closure) {
            $target($query, $direction);
        } else {
            $query->orderBy($target, $direction);
        }

        // Stable tiebreaker: without it, rows sharing a sort value can shuffle
        // between pages and appear duplicated or missing.
        $query->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');
    }

    /**
     * State handed to the view so headers can render their arrow and link.
     *
     * @return array{column: string, direction: string}
     */
    public function state(Request $request): array
    {
        return [
            'column' => $this->column($request),
            'direction' => $this->direction($request),
        ];
    }
}
