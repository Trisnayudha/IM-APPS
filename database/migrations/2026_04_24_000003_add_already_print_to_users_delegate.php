<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlreadyPrintToUsersDelegate extends Migration
{
    public function up()
    {
        Schema::table('users_delegate', function (Blueprint $table) {
            $table->boolean('already_print')->default(false)->after('image');
        });
    }

    public function down()
    {
        Schema::table('users_delegate', function (Blueprint $table) {
            $table->dropColumn('already_print');
        });
    }
}
