<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkshopMetsoCheckinToUsersDelegate extends Migration
{
    public function up()
    {
        Schema::table('users_delegate', function (Blueprint $table) {
            $table->timestamp('workshop_metso_checkin_at')->nullable()->after('already_print');
        });
    }

    public function down()
    {
        Schema::table('users_delegate', function (Blueprint $table) {
            $table->dropColumn('workshop_metso_checkin_at');
        });
    }
}
