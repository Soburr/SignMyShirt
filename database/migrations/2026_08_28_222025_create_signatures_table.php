<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shirt_id')->constrained()->cascadeOnDelete();

            $table->enum('side', ['front', 'back']);
            $table->enum('mode', ['typed', 'drawn']); 
            $table->float('x');
            $table->float('y');
            $table->float('rotation')->default(0);

            $table->string('color')->default('#1d1d1d');

            $table->string('typed_text')->nullable();

            $table->longText('drawn_path')->nullable();

            $table->string('signer_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};