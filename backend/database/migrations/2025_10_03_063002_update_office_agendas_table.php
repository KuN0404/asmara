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
        Schema::table('office_agendas', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('metting_link')->constrained('rooms')->onDelete('set null');
            $table->softDeletes();
        });

        // Pivot table untuk participants many-to-many
        Schema::create('office_agenda_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_agenda_id')->constrained('office_agendas')->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot table untuk attachments many-to-many
        Schema::create('office_agenda_attachment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_agenda_id')->constrained('office_agendas')->onDelete('cascade');
            $table->foreignId('attachment_id')->constrained('attachments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_agenda_attachment');
        Schema::dropIfExists('office_agenda_participant');

        Schema::table('office_agendas', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
            $table->dropSoftDeletes();
        });
    }
};
