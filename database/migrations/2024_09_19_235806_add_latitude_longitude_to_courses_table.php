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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('latitudeRamassage', 10, 8)->nullable()->after('adresseRamassage');
            $table->decimal('longitudeRamassage', 11, 8)->nullable()->after('latitudeRamassage');
            $table->decimal('latitudeLivraison', 10, 8)->nullable()->after('adresseLivraison');
            $table->decimal('longitudeLivraison', 11, 8)->nullable()->after('latitudeLivraison');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['latitudeRamassage', 'longitudeRamassage', 'latitudeLivraison', 'longitudeLivraison']);
        });
    }
};
