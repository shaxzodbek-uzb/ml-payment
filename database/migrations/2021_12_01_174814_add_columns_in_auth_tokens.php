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
            $table->string('code');
            $table->string('instanceId')->default('');
            $table->string('access_token')->default('');
            $table->string('refresh_token')->default('');
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