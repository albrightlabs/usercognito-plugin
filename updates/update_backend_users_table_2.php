<?php namespace Pfm\Ministry\Updates;

use Db;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateBackendUsersTable2 Migration
 */
class UpdateBackendUsersTable2 extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->integer('is_cognito_user_existing')->nullable();
        });
    }

    public function down()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->dropColumn('is_cognito_user_existing');
        });
    }
}
