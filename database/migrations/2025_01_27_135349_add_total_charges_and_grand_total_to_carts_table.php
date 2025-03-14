<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalChargesAndGrandTotalToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('total_charges', 10, 2)->default(0.00)->after('cart_total');
            $table->decimal('grand_total', 10, 2)->default(0.00)->after('total_charges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('total_charges');
            $table->dropColumn('grand_total');
        });
    }
}
