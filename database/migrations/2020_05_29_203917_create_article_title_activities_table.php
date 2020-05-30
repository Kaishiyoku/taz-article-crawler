<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTitleActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_title_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_guid');
            $table->string('title');
            $table->string('sub_title');
            $table->text('description');
            $table->timestamp('created_at')->nullable();

            $table->foreign('article_guid')->references('guid')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_title_activities');
    }
}
