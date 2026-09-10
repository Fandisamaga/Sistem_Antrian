<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_counters', function (Blueprint $table) {
            $table->foreignId('meja_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['meja_id', 'queue_date']);
        });
    }

    public function down(): void
    {
        Schema::table('queue_counters', function (Blueprint $table) {
            $table->dropUnique(['meja_id', 'queue_date']);
            $table->dropForeign(['meja_id']);
            $table->dropColumn('meja_id');
        });
    }
};
