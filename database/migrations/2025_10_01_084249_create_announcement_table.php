<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcement', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // saya ingin membuat kolom nya dengan fitu text yang bisa diatur ke bulet oin rat aanan kiri dll
            $table->text('media'); // saya ingin membua meda jika ada lampiran atau ingn display gambar aja di bagia pemberitahuannya
            $table->boolean('is_displayed')->default(false);
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement');
    }
};