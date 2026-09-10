<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Meja;
use App\Models\QueueCounter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Buat Daftar Layanan Standar Dukcapil
        $layananList = [
            [
                'nama_layanan' => 'Perekaman & Cetak KTP-el',
                'kode_layanan' => 'KTP',
                'deskripsi' => 'Pelayanan perekaman biometrik baru dan cetak KTP elektronik hilang atau rusak.',
            ],
            [
                'nama_layanan' => 'Kartu Keluarga (KK)',
                'kode_layanan' => 'KK',
                'deskripsi' => 'Penerbitan baru, penambahan anggota keluarga, serta pembaharuan data KK.',
            ],
            [
                'nama_layanan' => 'Akta Kelahiran & Kematian',
                'kode_layanan' => 'AKTA',
                'deskripsi' => 'Pencatatan sipil penerbitan kutipan akta kelahiran dan akta kematian.',
            ],
            [
                'nama_layanan' => 'Surat Pindah & Datang',
                'kode_layanan' => 'PINDAH',
                'deskripsi' => 'Surat keterangan pindah WNI antar wilayah dan kedatangan penduduk.',
            ],
            [
                'nama_layanan' => 'Kartu Identitas Anak (KIA)',
                'kode_layanan' => 'KIA',
                'deskripsi' => 'Penerbitan identitas resmi anak usia 0 sampai dengan 17 tahun kurang satu hari.',
            ],
            [
                'nama_layanan' => 'Informasi & Pengaduan',
                'kode_layanan' => 'INFO',
                'deskripsi' => 'Konsultasi berkas kependudukan, pemadanan NIK, dan penanganan aduan warga.',
            ],
        ];

        $createdLayanans = [];
        foreach ($layananList as $data) {
            $createdLayanans[] = Layanan::updateOrCreate(
                ['kode_layanan' => $data['kode_layanan']],
                [
                    'nama_layanan' => $data['nama_layanan'],
                    'deskripsi' => $data['deskripsi'],
                    'is_active' => true,
                ],
            );
        }

        // 2. Buat Meja 01 s/d Meja 20 dengan nomor meja dan tautan layanan default
        $totalLayanans = count($createdLayanans);
        foreach (range(1, 20) as $number) {
            $nomorStr = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $layananIndex = ($number - 1) % $totalLayanans;

            Meja::updateOrCreate(
                ['nomor_meja' => $number],
                [
                    'nama_meja' => 'Meja '.$nomorStr,
                    'layanan_id' => $createdLayanans[$layananIndex]->id,
                ],
            );
        }

        // 3. Inisialisasi Counter Hari Ini
        QueueCounter::firstOrCreate(
            ['queue_date' => Carbon::today()->toDateString()],
            ['current_number' => 0],
        );

        // 4. Akun Super Admin & CS
        User::updateOrCreate(
            ['email' => 'admin@dukcapil.test'],
            [
                'name' => 'Super Admin Dukcapil',
                'password' => 'password',
                'role' => 'admin',
                'meja_id' => null,
            ],
        );

        User::updateOrCreate(
            ['email' => 'cs@dukcapil.test'],
            [
                'name' => 'Petugas Customer Service',
                'password' => 'password',
                'role' => 'cs',
                'meja_id' => null,
            ],
        );

        // 5. Akun Operator untuk Seluruh Meja (Meja 01 s/d Meja 20)
        $allMejas = Meja::orderBy('nomor_meja')->get();
        foreach ($allMejas as $meja) {
            $nomorStr = str_pad((string) $meja->nomor_meja, 2, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['email' => "operator{$nomorStr}@dukcapil.test"],
                [
                    'name' => "Operator {$nomorStr}",
                    'password' => 'password',
                    'role' => 'operator',
                    'meja_id' => $meja->id,
                ],
            );
        }
    }
}
