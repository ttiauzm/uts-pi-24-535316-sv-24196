<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('director');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['ingin_ditonton', 'sedang_ditonton', 'sudah_ditonton'])
                ->default('ingin_ditonton');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
