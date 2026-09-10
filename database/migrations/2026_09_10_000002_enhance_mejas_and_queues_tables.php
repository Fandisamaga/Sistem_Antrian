<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->unsignedInteger('nomor_meja')->default(1)->after('id')->index();
            $table->foreignId('layanan_id')->nullable()->after('nama_meja')->constrained('layanans')->nullOnDelete();
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique(['queue_number']);
            $table->date('queue_date')->nullable()->after('queue_number')->index();
            $table->foreignId('layanan_id')->nullable()->after('meja_id')->constrained('layanans')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->after('layanan_id')->constrained('users')->nullOnDelete();
            $table->timestamp('called_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('called_at');
            $table->unsignedInteger('wait_duration')->nullable()->after('completed_at')->comment('Waktu tunggu dalam detik');
            $table->unsignedInteger('serve_duration')->nullable()->after('wait_duration')->comment('Waktu pelayanan dalam detik');

            $table->index('queue_number');
            $table->index(['queue_date', 'status']);
            $table->index(['operator_id', 'status']);
        });

        Schema::table('queue_counters', function (Blueprint $table) {
            $table->date('queue_date')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('queue_counters', function (Blueprint $table) {
            $table->dropColumn('queue_date');
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropIndex(['queue_number']);
            $table->dropIndex(['queue_date', 'status']);
            $table->dropIndex(['operator_id', 'status']);
            $table->dropForeign(['layanan_id']);
            $table->dropForeign(['operator_id']);
            $table->dropColumn([
                'queue_date',
                'layanan_id',
                'operator_id',
                'called_at',
                'completed_at',
                'wait_duration',
                'serve_duration',
            ]);
            $table->unique('queue_number');
        });

        Schema::table('mejas', function (Blueprint $table) {
            $table->dropForeign(['layanan_id']);
            $table->dropColumn(['nomor_meja', 'layanan_id']);
        });
    }
};

