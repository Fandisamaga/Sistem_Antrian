<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Meja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OperatorController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $operators = User::where('role', 'operator')
            ->with(['meja.layanan'])
            ->withCount([
                'queues as today_completed' => function ($q) use ($today) {
                    $q->where('status', 'completed')
                        ->where(function ($query) use ($today) {
                            $query->whereDate('created_at', $today)
                                ->orWhere('queue_date', $today);
                        });
                },
                'queues as total_served' => function ($q) {
                    $q->whereIn('status', ['completed', 'skipped']);
                },
            ])
            ->orderBy('name')
            ->get();

        $mejas = Meja::with(['layanan', 'user'])
            ->withCount(['queues as today_queues' => function ($q) use ($today) {
                $q->where(function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                        ->orWhere('queue_date', $today);
                });
            }])
            ->orderBy('nomor_meja')
            ->get();

        $layanans = Layanan::where('is_active', true)->orderBy('nama_layanan')->get();

        return view('admin.operators.index', [
            'operators' => $operators,
            'mejas' => $mejas,
            'layanans' => $layanans,
        ]);
    }

    public function storeOperator(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'meja_id' => ['nullable', 'exists:mejas,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'operator',
            'meja_id' => $validated['meja_id'] ?: null,
        ]);

        return redirect()->route('admin.operators.index')
            ->with('success', 'Akun operator berhasil dibuat.');
    }

    public function updateOperator(Request $request, User $user)
    {
        abort_unless($user->role === 'operator', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'meja_id' => ['nullable', 'exists:mejas,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'meja_id' => $validated['meja_id'] ?: null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.operators.index')
            ->with('success', 'Data akun operator berhasil diperbarui.');
    }

    public function destroyOperator(User $user)
    {
        abort_unless($user->role === 'operator', 403);

        $user->delete();

        return redirect()->route('admin.operators.index')
            ->with('success', 'Akun operator berhasil dihapus.');
    }

    public function storeMeja(Request $request)
    {
        $validated = $request->validate([
            'nama_meja' => ['required', 'string', 'max:100'],
            'nomor_meja' => ['required', 'integer', 'min:1'],
            'layanan_id' => ['nullable', 'exists:layanans,id'],
        ]);

        Meja::create($validated);

        return redirect()->route('admin.operators.index')
            ->with('success', 'Meja pelayanan berhasil ditambahkan.');
    }

    public function updateMeja(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'nama_meja' => ['required', 'string', 'max:100'],
            'nomor_meja' => ['required', 'integer', 'min:1'],
            'layanan_id' => ['nullable', 'exists:layanans,id'],
        ]);

        $meja->update($validated);

        return redirect()->route('admin.operators.index')
            ->with('success', 'Data meja pelayanan berhasil diperbarui.');
    }

    public function destroyMeja(Meja $meja)
    {
        if ($meja->queues()->exists()) {
            return redirect()->route('admin.operators.index')
                ->with('error', 'Meja tidak dapat dihapus karena sudah memiliki data antrean.');
        }

        $meja->delete();

        return redirect()->route('admin.operators.index')
            ->with('success', 'Meja berhasil dihapus.');
    }
}

