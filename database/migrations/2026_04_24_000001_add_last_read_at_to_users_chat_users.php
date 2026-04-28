<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastReadAtToUsersChatUsers extends Migration
{
    public function up()
    {
        Schema::table('users_chat_users', function (Blueprint $table) {
            $table->timestamp('last_read_at')->nullable()->after('target_id');
        });
    }

    public function down()
    {
        Schema::table('users_chat_users', function (Blueprint $table) {
            $table->dropColumn('last_read_at');
        });
    }
}
