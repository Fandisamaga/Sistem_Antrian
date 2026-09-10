<?php

use App\Models\Layanan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $officialServices = [
            [
                'nama_layanan' => 'Kartu Keluarga',
                'kode_layanan' => 'KK',
                'deskripsi' => 'Penerbitan baru, penambahan anggota keluarga, dan perubahan data KK.',
            ],
            [
                'nama_layanan' => 'Akta Kelahiran',
                'kode_layanan' => 'AK-LHR',
                'deskripsi' => 'Pencatatan kelahiran dan penerbitan kutipan akta kelahiran baru atau hilang/rusak.',
            ],
            [
                'nama_layanan' => 'Akta Kematian',
                'kode_layanan' => 'AK-KMT',
                'deskripsi' => 'Pencatatan dan penerbitan kutipan akta kematian bagi warga.',
            ],
            [
                'nama_layanan' => 'Akta Perkawinan',
                'kode_layanan' => 'AK-KWN',
                'deskripsi' => 'Pencatatan perkawinan non-muslim dan penerbitan kutipan akta perkawinan.',
            ],
            [
                'nama_layanan' => 'Akta Perceraian',
                'kode_layanan' => 'AK-CR',
                'deskripsi' => 'Pencatatan putusan perceraian pengadilan dan penerbitan akta perceraian.',
            ],
            [
                'nama_layanan' => 'Surat Pindah',
                'kode_layanan' => 'PINDAH',
                'deskripsi' => 'Penerbitan Surat Keterangan Pindah WNI (SKPWNI) antar wilayah dan kedatangan penduduk.',
            ],
            [
                'nama_layanan' => 'Biodata Penduduk Non Permanen',
                'kode_layanan' => 'BIO-NON',
                'deskripsi' => 'Pendaftaran dan penerbitan bukti biodata bagi penduduk non-permanen.',
            ],
            [
                'nama_layanan' => 'KIA (Kartu Identitas Anak)',
                'kode_layanan' => 'KIA',
                'deskripsi' => 'Penerbitan identitas resmi anak usia 0 sampai dengan 17 tahun kurang satu hari.',
            ],
        ];

        // 1. Insert or update the 8 official services
        $activeCodes = [];
        foreach ($officialServices as $service) {
            $activeCodes[] = $service['kode_layanan'];
            Layanan::updateOrCreate(
                ['kode_layanan' => $service['kode_layanan']],
                [
                    'nama_layanan' => $service['nama_layanan'],
                    'deskripsi' => $service['deskripsi'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Safely clean up / deactivate old services not in the 8 official list
        $oldLayanans = Layanan::whereNotIn('kode_layanan', $activeCodes)->get();
        foreach ($oldLayanans as $old) {
            if ($old->queues()->exists()) {
                $old->update(['is_active' => false]);
            } else {
                $old->delete();
            }
        }
    }

    public function down(): void
    {
        // Reversible if needed
    }
};
