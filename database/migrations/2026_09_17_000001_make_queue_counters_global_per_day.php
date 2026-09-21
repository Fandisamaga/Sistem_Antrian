<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Counter lama bersifat per meja. Nilainya dapat diturunkan kembali dari
        // tiket, jadi ganti dengan satu counter unik untuk setiap tanggal.
        $counters = DB::table('queues')
            ->selectRaw('COALESCE(queue_date, DATE(created_at)) as queue_date, MAX(CAST(queue_number AS UNSIGNED)) as current_number')
            ->where(function ($query) {
                $query->whereNotNull('queue_date')
                    ->orWhereNotNull('created_at');
            })
            ->groupByRaw('COALESCE(queue_date, DATE(created_at))')
            ->get();

        Schema::dropIfExists('queue_counters');

        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date')->unique();
            $table->unsignedBigInteger('current_number')->default(0);
        });

        foreach ($counters as $counter) {
            DB::table('queue_counters')->insert([
                'queue_date' => $counter->queue_date,
                'current_number' => $counter->current_number,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_counters');

        // Bentuk ini diperlukan oleh migrasi sebelumnya bila proyek di-rollback.
        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date')->nullable()->index();
            $table->foreignId('meja_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unique(['meja_id', 'queue_date']);
        });
    }
};
