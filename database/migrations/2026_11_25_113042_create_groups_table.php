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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('currency', 3)->default('KES');
            $table->string('interest_method')->default('flat');
            $table->unsignedSmallInteger('interest_rate_bps')->default(1000);
            $table->unsignedSmallInteger('late_penalty_bps')->default(0);
            $table->unsignedTinyInteger('loan_limit_multiplier')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
