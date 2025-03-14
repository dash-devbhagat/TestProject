<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deals_redeems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_redeemed')->default(false);
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('mobile_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals_redeems');
    }
};
