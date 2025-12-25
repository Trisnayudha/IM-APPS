<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkingQuotaTable extends Migration
{
    public function up()
    {
        Schema::create('networking_quota', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('events_id');
            $table->integer('total_quota')->default(5);
            $table->integer('used_quota')->default(0);
            $table->date('reset_date')->nullable();
            $table->timestamps();

            $table->unique(['users_id', 'events_id'], 'uniq_quota_user_event');

            $table->foreign('users_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('events_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('networking_quota');
    }
}
