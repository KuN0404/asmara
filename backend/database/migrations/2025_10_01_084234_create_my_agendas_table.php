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
            $table->boolean('is_show_to_other')->default(false);
            $table->enum('status', [
                'comming_soon',
                'in_progress',
                'schedule_change',
                'completed',
                'cancelled'
            ])->default('comming_soon');
            // 🔸 Relasi ke tabel users
            $table->foreignId('created_by')
                ->constrained('users') // default: references('id')->on('users')
                ->onDelete('cascade'); // jika user dihapus, agenda ikut terhapus
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
