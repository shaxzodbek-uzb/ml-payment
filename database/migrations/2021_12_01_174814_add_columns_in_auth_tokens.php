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
            $table->text('code')->default('');
            $table->text('instanceId')->default('');
            $table->text('access_token')->default('');
            $table->text('refresh_token')->default('');
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