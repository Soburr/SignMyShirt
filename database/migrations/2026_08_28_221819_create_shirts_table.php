<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shirts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('creator_name')->nullable();
            $table->text('front_text')->nullable();
            $table->string('front_text_color')->nullable();
            $table->string('front_image_path')->nullable();
            $table->float('front_x')->nullable();   
            $table->float('front_y')->nullable();   
            $table->float('front_width')->nullable(); 
            $table->float('front_height')->nullable();

            $table->text('back_text')->nullable();
            $table->string('back_text_color')->nullable();
            $table->string('back_image_path')->nullable();
            $table->float('back_x')->nullable();
            $table->float('back_y')->nullable();
            $table->float('back_width')->nullable();
            $table->float('back_height')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shirts');
    }
};