<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkingRequestTable extends Migration
{
    public function up()
    {
        Schema::create('networking_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('events_id');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->unique(['requester_id', 'target_id', 'events_id'], 'uniq_networking_request');

            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('target_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('events_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('networking_request');
    }
}
