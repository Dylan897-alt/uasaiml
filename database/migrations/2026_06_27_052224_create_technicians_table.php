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
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('configuration_id')
                ->constrained('saved_technician_configurations')
                ->cascadeOnDelete();

            $table->string('team_name');

            $table->enum('specialization', [
                'condition',
                'all'
            ]);

            $table->string('operator')->nullable();

            $table->integer('mw')->nullable();

            $table->decimal('cost', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
