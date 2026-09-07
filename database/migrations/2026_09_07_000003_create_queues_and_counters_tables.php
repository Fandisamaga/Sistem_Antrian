<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number')->unique();
            $table->foreignId('meja_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('waiting')->index();
            $table->timestamps();

            $table->index(['meja_id', 'status']);
        });

        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('current_number')->default(0);
        });

        DB::table('queue_counters')->insert([
            'id' => 1,
            'current_number' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
        Schema::dropIfExists('queue_counters');
    }
};
