<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->enum('type', ['string', 'integer', 'boolean', 'decimal'])->default('string');
            $table->timestamps();
        });

        // Insert default configurations
        DB::table('configs')->insert([
            [
                'key' => 'max_students_per_supervisor',
                'value' => '8',
                'description' => 'Maximum number of students that can be assigned to a supervisor',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'application_deadline',
                'value' => '2024-12-31',
                'description' => 'Deadline for project allocation applications',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'allocation_enabled',
                'value' => 'true',
                'description' => 'Whether project allocation is currently enabled',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
