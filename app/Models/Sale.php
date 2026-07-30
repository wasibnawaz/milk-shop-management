<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'dealer_id',
        'user_id',
        'customer_name',
        'quantity',
        'unit_rate',
        'amount_paid',
        'sale_date',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'sale_date' => 'date',
            'payment_status' => PaymentStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Derived attributes
    |--------------------------------------------------------------------------
    |
    | total_amount and payment_status are deliberately NOT fillable. They are
    | derived here on every write so a caller cannot submit a total that
    | disagrees with quantity * rate, or a "paid" status with nothing paid.
    */

    protected static function booted(): void
    {
        static::saving(function (self $sale): void {
            $sale->total_amount = round((float) $sale->quantity * (float) $sale->unit_rate, 2);

            // Never let more be recorded as paid than the sale is worth.
            $sale->amount_paid = min(
                max((float) $sale->amount_paid, 0),
                (float) $sale->total_amount
            );

            $sale->payment_status = match (true) {
                (float) $sale->amount_paid <= 0 => PaymentStatus::Unpaid,
                (float) $sale->amount_paid >= (float) $sale->total_amount => PaymentStatus::Paid,
                default => PaymentStatus::Partial,
            };
        });
    }

    public function getOutstandingAttribute(): float
    {
        return round((float) $this->total_amount - (float) $this->amount_paid, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Dealer, $this> */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /** @param  Builder<$this>  $query */
    public function scopeBetween(Builder $query, ?string $from, ?string $to): void
    {
        $query->when($from, fn (Builder $q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('sale_date', '<=', $to));
    }

    /** @param  Builder<$this>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $q) use ($term): void {
            $q->where('customer_name', 'like', "%{$term}%")
                ->orWhere('notes', 'like', "%{$term}%")
                ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', "%{$term}%"))
                ->orWhereHas('dealer', fn (Builder $d) => $d->where('name', 'like', "%{$term}%"));
        });
    }
}
