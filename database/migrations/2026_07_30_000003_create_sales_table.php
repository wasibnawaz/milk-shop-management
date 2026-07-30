<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();

            // Who recorded the sale. Populated once authentication lands in
            // Milestone 2; nullable so existing rows and seeds stay valid.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name')->nullable();

            // 3dp so partial litres (e.g. 0.500 L) are representable.
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_rate', 10, 2);

            // Denormalised on purpose: quantity * unit_rate is recomputed on
            // every write, but storing it keeps historical totals correct if a
            // product's rate changes later, and lets the DB aggregate directly.
            $table->decimal('total_amount', 12, 2);

            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('payment_status')->default('paid');

            $table->date('sale_date');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('sale_date');
            $table->index('payment_status');
            // Covers the dashboard's "sales for a period, by product" query.
            $table->index(['sale_date', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
