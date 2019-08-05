<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('商品名称');
            $table->string('barcode')->comment('条形码数据');
            $table->string('price')->nullable()->comment('线上售价');
            $table->string('brand')->nullable()->comment('品牌');
            $table->string('supplier')->nullable()->comment('供应商');
            $table->string('standard')->nullable()->comment('规格');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
