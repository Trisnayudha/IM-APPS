<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailNotificationQueueTable extends Migration
{
    public function up()
    {
        Schema::create('email_notification_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->enum('type', ['unread_message', 'connection_request', 'meeting_request']);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->json('payload')->nullable();
            $table->tinyInteger('is_processed')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'is_processed']);
            $table->index('created_at');

            $table->foreign('recipient_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_notification_queue');
    }
}
