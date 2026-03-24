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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('entretien_id')->constrained('entretiens')->onDelete('cascade');
            $table->unsignedTinyInteger('note'); // 1 à 5
            $table->text('commentaire')->nullable();
            $table->timestamps();

            // Un talent ne peut laisser qu'un seul feedback par entretien
            $table->unique(['talent_id', 'entretien_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
