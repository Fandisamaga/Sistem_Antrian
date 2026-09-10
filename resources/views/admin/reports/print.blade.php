<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja Antrean Dukcapil Kota Palu</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.3;
        }
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 75px;
            height: auto;
            margin-right: 18px;
        }
        .kop-text {
            text-align: center;
            flex: 1;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-text p {
            margin: 2px 0;
            font-size: 9pt;
        }
        .judul-laporan {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-laporan h3 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .judul-laporan p {
            margin: 4px 0 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .summary-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-item {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            background: #fafafa;
        }
        .summary-item .label {
            font-size: 8pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-item .value {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 4px;
        }
        .ttd-wrapper {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            width: 250px;
        }
        .ttd-space {
            height: 70px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #e2e8f0; padding: 12px; text-align: center; margin-bottom: 20px; border-radius: 8px;">
        <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 10px 24px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer;">
            🖨️ Cetak / Simpan sebagai PDF
        </button>
        <button onclick="window.close()" style="background: #64748b; color: #fff; border: none; padding: 10px 18px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat">
        <img src="{{ asset('Logo.png') }}" alt="Logo Dinas" class="kop-logo">
        <div class="kop-text">
            <h2>Pemerintah Kota Palu</h2>
            <h1>Dinas Kependudukan dan Pencatatan Sipil</h1>
            <p>Jalan Balai Kota No. 1, Kota Palu, Sulawesi Tengah</p>
            <p>Email: dukcapil@palukota.go.id &bull; Website: dukcapil.palukota.go.id</p>
        </div>
    </div>

    <!-- Judul Laporan -->
    <div class="judul-laporan">
        <h3>Laporan Rekapitulasi Pelayanan Antrean & Kinerja Petugas Operator</h3>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d F Y') }}</strong></p>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Total Antrean</div>
            <div class="value">{{ $totalPeriod }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Selesai Dilayani</div>
            <div class="value">{{ $completedPeriod }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Rata-Rata Layanan</div>
            <div class="value">{{ $avgServeFormatted }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Rata-Rata Tunggu</div>
            <div class="value">{{ $avgWaitFormatted }}</div>
        </div>
    </div>

    <!-- Tabel 1: Evaluasi Kinerja Operator -->
    <h4 style="margin: 15px 0 8px; font-size: 11pt; text-transform: uppercase;">I. Evaluasi Kinerja Masing-Masing Petugas Operator Loket</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Petugas Operator</th>
                <th>Meja Loket</th>
                <th style="width: 80px;">Total Dilayani</th>
                <th style="width: 70px;">Selesai</th>
                <th style="width: 70px;">Dilewati</th>
                <th style="width: 80px;">Tingkat Sukses</th>
                <th style="width: 90px;">Rata2 Layanan</th>
                <th style="width: 90px;">Rata2 Tunggu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($operatorPerformance as $index => $op)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-bold">{{ $op['operator']->name }}</td>
                    <td>{{ $op['meja'] }}</td>
                    <td class="text-center text-bold">{{ $op['total_handled'] }}</td>
                    <td class="text-center">{{ $op['completed'] }}</td>
                    <td class="text-center">{{ $op['skipped'] }}</td>
                    <td class="text-center">{{ $op['completion_rate'] }}%</td>
                    <td class="text-center">{{ $op['avg_serve_formatted'] }}</td>
                    <td class="text-center">{{ $op['avg_wait_formatted'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data aktivitas operator pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tabel 2: Riwayat Log Transaksi Antrean -->
    <h4 style="margin: 25px 0 8px; font-size: 11pt; text-transform: uppercase;">II. Arsip Log Pelayanan Antrean Terpilih</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 75px;">Nomor</th>
                <th>Waktu Ambil</th>
                <th>Meja Loket</th>
                <th>Jenis Pelayanan</th>
                <th>Operator</th>
                <th style="width: 70px;">Status</th>
                <th style="width: 75px;">Durasi Layanan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($queues as $i => $q)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center text-bold">{{ $q->queue_number }}</td>
                    <td class="text-center">{{ $q->created_at->format('d/m/y H:i') }}</td>
                    <td>{{ $q->meja?->nama_meja ?? '-' }}</td>
                    <td>{{ $q->layanan?->nama_layanan ?? '-' }}</td>
                    <td>{{ $q->operator?->name ?? '-' }}</td>
                    <td class="text-center" style="text-transform: capitalize;">{{ $q->status }}</td>
                    <td class="text-center">{{ $q->formatted_serve_duration }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data riwayat antrean.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Penanggung Jawab -->
    <div class="ttd-wrapper">
        <div class="ttd-box">
            <p style="margin-bottom: 2px;">Palu, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</p>
            <p style="margin-top: 0;">Kepala Dinas Kependudukan &<br>Pencatatan Sipil Kota Palu</p>
            <div class="ttd-space"></div>
            <p style="margin-bottom: 2px; font-weight: bold; text-decoration: underline;">H. WALMIN, S.Sos., M.Si.</p>
            <p style="margin-top: 0; font-size: 9pt;">NIP. 19700815 199603 1 004</p>
        </div>
    </div>
</body>
</html>

