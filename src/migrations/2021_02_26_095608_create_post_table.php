<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('post', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('media_id')->default(0);
            $table->string('title');
            $table->text('description');
            $table->unsignedTinyInteger('status')->comment('0 - Draft, 1 -  success');
            $table->unsignedTinyInteger('type')->comment(' 1 - Article , 2 - Image Based 3 - Video Based');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('slug')->nullable();
            $table->string('meta_keyword')->nullable();
            $table->unsignedTinyInteger('active');
            $table->timestamp('publish_utc')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->index('active');
            $table->index('created_by');
            $table->index('media_id');
            $table->index('status');
            $table->index('type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post');
    }
}
