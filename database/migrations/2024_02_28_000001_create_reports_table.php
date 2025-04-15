<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->string('project_code');
            $table->string('location');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_overtime')->default(false);
            $table->boolean('is_shift')->default(false);
            $table->enum('work_day_type', ['Hari Kerja', 'Hari Libur']);
            $table->enum('status', [
                'Draft',
                'Laporan tanpa Lembur',
                'Menunggu Verifikasi',
                'Ditolak Verifikator',
                'Menunggu Approval VP',
                'Ditolak VP',
                'Menunggu Review HR',
                'Ditolak HR',
                'Selesai'
            ])->default('Draft');
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vp_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('can_revise')->default(false);
            $table->text('rejection_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->enum('status', ['Selesai', 'Dalam Proses', 'Tertunda', 'Bermasalah']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_details');
        Schema::dropIfExists('reports');
    }
}; 