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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fournisseur_id');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('adresseRamassage');
            $table->string('adresseLivraison');
            $table->string('emailClient');
            $table->enum('typeDeCourse', ['Normal', 'Express', 'Fragile']);
            $table->string('statut')->default('Disponible');
            $table->timestamps();

            // Foreign keys
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
