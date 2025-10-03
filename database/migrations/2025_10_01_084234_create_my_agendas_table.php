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
        Schema::create('my_agendas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_at');
            $table->dateTime('until_at');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_show_to_other');
            $table->enum('status', ['comming_soon', 'in_progress', 'schedule_change','completed', 'cancelled']);
            $table->string('ceated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_agendas');
    }
};