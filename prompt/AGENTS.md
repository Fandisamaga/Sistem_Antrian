<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

# AI Agent Behavior & Coding Guidelines

## Gaya Penulisan Kode
- **Langsung ke Solusi:** Jangan berikan penjelasan panjang lebar sebelum memberikan kode. Berikan kode yang terstruktur, lalu jelaskan poin pentingnya di bawah.
- **KISS (Keep It Simple, Stupid):** Hindari abstraksi berlebihan (over-engineering). Gunakan arsitektur MVC standar Laravel kecuali disuruh sebaliknya.
- **Bahasa:** Gunakan penamaan variabel dan tabel bahasa Inggris (contoh: `queues`, `tables/mejas`, `queue_number`), namun gunakan Bahasa Indonesia untuk UI text dan komentar jika diperlukan.

## Standar UI/UX (Tailwind CSS)
- Desain antarmuka harus meniru mesin ATM atau terminal Point of Sale (POS).
- Gunakan utility classes Tailwind untuk membuat tombol yang sangat besar, *padding* luas, dan *font* tebal (contoh: `text-4xl font-bold py-6 px-12`).
- Hindari penggunaan bayangan tipis atau elemen visual yang mengganggu. Gunakan solid colors: Biru (Primary/Panggil), Merah (Lewati/Danger), Hijau (Selesai/Success).
- Hilangkan *scrollbars* yang tidak perlu di halaman Display TV.

## Keamanan & Stabilitas
- Gunakan DB Transaction atau Lock saat meng-generate `queue_number` agar tidak ada warga yang mendapat nomor ganda pada waktu yang bersamaan.
- Validasi ketat akses halaman: CS tidak boleh membuka halaman Operator, dan Operator Meja 01 tidak bisa mengubah status antrean Meja 02.

## Manajemen State UI (Inertia.js)
- Manfaatkan fitur navigasi Inertia agar form submission (klik meja oleh CS atau klik panggil oleh Operator) tidak membuat halaman berkedip (*no full page reload*).
- Gunakan fungsi `preserveScroll` dan `preserveState` pada Inertia saat melakukan pembaruan antrean.