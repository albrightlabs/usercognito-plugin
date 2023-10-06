<?php namespace Pfm\Ministry\Updates;

use Db;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateBackendUsersTable Migration
 */
class UpdateBackendUsersTable extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->integer('is_cognito_user')->nullable();
        });
    }

    public function down()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->dropColumn('is_cognito_user');
        });
    }
}
