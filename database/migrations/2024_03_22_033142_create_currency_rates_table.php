<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('source_currency', 3);
            $table->string('currency', 3);
            $table->decimal('rate', 18, 6);
            $table->timestamps();

            $table->unique(['date', 'source_currency', 'currency'], 'date_currency_pair_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
