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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->string('direction', 3);
            $table->unsignedBigInteger('amount');
            $table->string('reference', 40)->nullable();
            $table->date('occurred_on');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reverses_entry_id')->nullable()->constrained('ledger_entries')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['group_id', 'occurred_on']);
            $table->index(['group_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
