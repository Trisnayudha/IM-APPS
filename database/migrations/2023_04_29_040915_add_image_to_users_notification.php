<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToUsersNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_notification', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->string('type')->nullable();
            $table->string('all_users')->nullable();
            $table->string('target_id')->nullable();
            $table->string('target_slug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_notification', function (Blueprint $table) {
            //
        });
    }
}
