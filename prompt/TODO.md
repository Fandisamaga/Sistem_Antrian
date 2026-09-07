# MVP Implementation Plan

## Phase 1: Setup & Database
- [ ] Install Laravel 11, Inertia.js, dan Tailwind CSS.
- [ ] Setup koneksi database MySQL.
- [ ] Buat skema tabel dan migration:
  - `mejas` (id, nama_meja).
  - `users` (tambahkan kolom `role` [cs, operator, admin] dan foreign key `meja_id` nullable).
  - `queues` (id, queue_number, meja_id, status [waiting, called, skipped, completed], created_at).
- [ ] Buat seeder untuk akun CS, 20+ akun Operator, dan 20+ Meja.

## Phase 2: Core Logic & Realtime Setup
- [ ] Install dan konfigurasikan Laravel Reverb.
- [ ] Buat `QueueService` untuk meng-handle logic pembuatan nomor antrean global secara aman (atomic/lock untuk mencegah duplikasi nomor).
- [ ] Buat event `QueueCalled` yang akan di-broadcast melalui Reverb.

## Phase 3: UI Customer Service (CS)
- [ ] Buat halaman `/cs` dengan UI Grid Buttons (Tombol Meja 01, Meja 02, dst).
- [ ] Buat endpoint POST untuk men-generate nomor saat tombol meja diklik.
- [ ] Implementasikan fitur cetak tiket thermal otomatis setelah nomor berhasil digenerate (tanpa me-reload halaman web).
- [ ] Desain layout tiket thermal (hanya tulisan: DUKCAPIL, NOMOR, MEJA).

## Phase 4: UI Operator Meja
- [ ] Buat halaman `/operator` (hanya bisa diakses jika role = operator).
- [ ] Buat query agar operator HANYA melihat antrean dengan `meja_id` miliknya yang berstatus `waiting`.
- [ ] Implementasikan tombol aksi:
  - `Panggil`: Ubah status jadi `called`, trigger event WebSockets.
  - `Panggil Ulang`: Trigger ulang event WebSockets tanpa ubah status.
  - `Lewati`: Ubah status jadi `skipped`.
  - `Selesai`: Ubah status jadi `completed`.

## Phase 5: UI Display TV & Audio (Front-Facing)
- [ ] Buat halaman `/display` dengan UI super besar (Nomor Antrean + Meja Tujuan) menggunakan Tailwind.
- [ ] Implementasikan listener WebSockets (Echo) untuk menangkap event `QueueCalled`.
- [ ] Buat logic audio player (menggabungkan file MP3 suara atau menggunakan Web Speech API TTS) saat event diterima.

## Phase 6: Testing & Polish
- [ ] Uji skenario bentrok data (concurrency) saat CS mengklik tombol secara brutal.
- [ ] Pastikan WebSockets berjalan lancar di jaringan lokal.
- [ ] Bersihkan kode dan pastikan UI responsif namun terkunci dengan baik di mode fullscreen untuk TV.