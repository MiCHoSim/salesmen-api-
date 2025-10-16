<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesmen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->json('titles_before')->nullable();
            $table->json('titles_after')->nullable();
            $table->string('prosight_id', 5)->unique();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['m', 'f']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index('prosight_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesmen');
    }
};
