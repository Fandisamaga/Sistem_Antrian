<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::withCount(['mejas', 'queues'])
            ->orderBy('nama_layanan')
            ->get();

        return view('admin.layanans.index', [
            'layanans' => $layanans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:150'],
            'kode_layanan' => ['required', 'string', 'max:20', 'unique:layanans,kode_layanan'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Layanan::create($validated);

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        $validated = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:150'],
            'kode_layanan' => ['required', 'string', 'max:20', Rule::unique('layanans')->ignore($layanan->id)],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $layanan->update($validated);

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Data layanan berhasil diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        // Jika sudah ada antrean terkait, lakukan soft toggle aktif/nonaktif agar riwayat tidak rusak
        if ($layanan->queues()->exists()) {
            $layanan->update(['is_active' => ! $layanan->is_active]);
            $status = $layanan->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

            return redirect()->route('admin.layanans.index')
                ->with('success', "Layanan {$status} karena memiliki riwayat antrean.");
        }

        $layanan->delete();

        return redirect()->route('admin.layanans.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}

