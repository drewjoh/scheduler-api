<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->increments('id');
            $table->integer('manager_id')->unsigned()->index();
            $table->integer('employee_id')->unsigned()->nullable()->index();
            $table->float('break')->nullable();
            $table->string('start_time')->index();
            $table->string('end_time')->index();
            $table->string('created_at');
            $table->string('updated_at');
        });
        
        // Foreign keys
        Schema::table('user_shifts', function(Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')->on('users');
            $table->foreign('employee_id')
                ->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_shifts');
    }
}
