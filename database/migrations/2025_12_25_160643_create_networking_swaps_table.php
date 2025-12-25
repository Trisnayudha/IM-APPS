<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkingSwapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networking_swaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('events_id');
            $table->enum('direction', ['left', 'right']);
            $table->timestamps();

            $table->unique(['users_id', 'target_id', 'events_id'], 'uniq_swap_user_target_event');

            $table->foreign('users_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('target_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('events_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('networking_swaps');
    }
}
