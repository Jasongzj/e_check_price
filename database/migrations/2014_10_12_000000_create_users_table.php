<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('store_id')->nullable()->comment('店铺id');
            $table->unsignedTinyInteger('is_manager')->default(0)->comment('是否店长');
            $table->string('openid');
            $table->string('nick_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->unsignedTinyInteger('gender')->default(0)->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
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
        Schema::dropIfExists('users');
    }
}
