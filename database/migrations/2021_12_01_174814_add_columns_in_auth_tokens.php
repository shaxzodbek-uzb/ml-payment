<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInAuthTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->string('code', 1000)->default('');
            $table->string('instanceId', 1000)->default('');
            $table->string('access_token', 1000)->default('');
            $table->string('refresh_token', 1000)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('instanceId');
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
        });
    }
}