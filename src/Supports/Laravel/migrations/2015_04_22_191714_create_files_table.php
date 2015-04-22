<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('location')->unique();
            if (DB::getDriverName() !== 'mysql') {
                $table->binary('content');
            }
            $table->boolean('visibility')->default(true);
            $table->timestamps();

        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `files` ADD `content` LONGBLOB;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('files');
    }
}