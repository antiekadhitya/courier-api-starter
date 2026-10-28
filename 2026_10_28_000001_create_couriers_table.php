<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->unsignedTinyInteger('level');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['level', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
