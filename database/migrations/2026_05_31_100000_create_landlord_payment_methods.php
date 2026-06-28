<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('method_type', ['card', 'ach', 'crypto', 'paypal', 'other']);
            $table->string('label')->nullable();
            $table->string('brand', 32)->nullable();
            $table->string('last4', 8)->nullable();
            $table->string('external_ref')->nullable();
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'pending', 'removed'])->default('active');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_payment_methods');
    }
};
