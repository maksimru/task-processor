<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    //table
    public $table = 'users';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection($this->getConnection())->create(
            $this->table,
            function (Blueprint $table) {
                $table->increments('user_id');
                $table->binary('api_key')->unique();
                $table->rememberToken();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection($this->getConnection())->drop($this->table);
    }
}
