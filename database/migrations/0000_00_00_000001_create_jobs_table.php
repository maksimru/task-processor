<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{

    //table
    public $table = 'jobs';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection($this->getConnection())->create(
            $this->table,
            function (Blueprint $table) {
                $table->increments('job_id');
                $table->unsignedInteger('submitter_id');
                $table->unsignedInteger('processor_id')->nullable();
                $table->boolean('is_processed')->index()->default(false);
                $table->boolean('is_locked')->index()->default(false);
                $table->unsignedBigInteger('processing_time')->nullable();
                $table->unsignedInteger('priority')->default(0);
                $table->string('payload');
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
