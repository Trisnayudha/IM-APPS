<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkingMeetingTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networking_meeting_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('events_id');
            $table->dateTime('schedule_date')->nullable();
            $table->integer('table_number')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('target_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('events_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('networking_meeting_tables');
    }
}
