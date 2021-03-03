<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostMediaLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('link');
            $table->unsignedTinyInteger('type')->comment("1 - Image 2 - Video 3 - Gif");
            $table->unsignedTinyInteger('active');
            $table->timestamps();
            $table->index('active');
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
        Schema::dropIfExists('post_media_links');
    }
}
