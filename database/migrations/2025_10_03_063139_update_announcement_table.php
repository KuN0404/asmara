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
        Schema::table('announcement', function (Blueprint $table) {
            $table->string('title')->after('id');
        });

        // Pivot table untuk announcement attachments
        Schema::create('announcement_attachment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcement')->onDelete('cascade');
            $table->foreignId('attachment_id')->constrained('attachments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_attachment');

        Schema::table('announcement', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};