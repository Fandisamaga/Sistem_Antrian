<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Meja;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);

        $queues = (clone $data['baseQuery'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', array_merge($data, [
            'queues' => $queues,
        ]));
    }

    public function print(Request $request)
    {
        $data = $this->buildReportData($request);

        $queues = (clone $data['baseQuery'])
            ->latest('id')
            ->limit(200)
            ->get();

        return view('admin.reports.print', array_merge($data, [
            'queues' => $queues,
        ]));
    }

    private function buildReportData(Request $request): array
    {
        $periode = $request->input('periode', 'today');
        $today = Carbon::today();

        if ($periode === 'week') {
            $dateFrom = $today->copy()->startOfWeek()->toDateString();
            $dateTo = $today->toDateString();
            $periodeLabel = 'Minggu Ini ('.Carbon::parse($dateFrom)->translatedFormat('d M').' - '.Carbon::parse($dateTo)->translatedFormat('d M Y').')';
        } elseif ($periode === 'month') {
            $dateFrom = $today->copy()->startOfMonth()->toDateString();
            $dateTo = $today->toDateString();
            $periodeLabel = 'Bulan Ini ('.Carbon::parse($dateFrom)->translatedFormat('F Y').')';
        } elseif ($periode === 'custom') {
            $dateFrom = $request->input('date_from', $today->toDateString());
            $dateTo = $request->input('date_to', $today->toDateString());
            $periodeLabel = Carbon::parse($dateFrom)->translatedFormat('d M Y').' s/d '.Carbon::parse($dateTo)->translatedFormat('d M Y');
        } else {
            $periode = 'today';
            $dateFrom = $today->toDateString();
            $dateTo = $today->toDateString();
            $periodeLabel = 'Hari Ini ('.$today->translatedFormat('d F Y').')';
        }

        $selectedLayananId = $request->input('layanan_id');
        $selectedMejaId = $request->input('meja_id');
        $selectedOperatorId = $request->input('operator_id');
        $selectedStatus = $request->input('status');

        $baseQuery = Queue::with(['meja', 'layanan', 'operator'])
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('queue_date', [$dateFrom, $dateTo])
                    ->orWhereBetween('created_at', [
                        Carbon::parse($dateFrom)->startOfDay(),
                        Carbon::parse($dateTo)->endOfDay(),
                    ]);
            });

        if ($selectedLayananId) {
            $baseQuery->where('layanan_id', $selectedLayananId);
        }

        if ($selectedMejaId) {
            $baseQuery->where('meja_id', $selectedMejaId);
        }

        if ($selectedOperatorId) {
            $baseQuery->where('operator_id', $selectedOperatorId);
        }

        if ($selectedStatus) {
            $baseQuery->where('status', $selectedStatus);
        }

        // Statistik Ringkasan Periode Terpilih
        $totalPeriod = (clone $baseQuery)->count();
        $completedPeriod = (clone $baseQuery)->where('status', 'completed')->count();
        $skippedPeriod = (clone $baseQuery)->where('status', 'skipped')->count();
        $waitingPeriod = (clone $baseQuery)->where('status', 'waiting')->count();
        $avgWaitPeriod = (clone $baseQuery)->whereNotNull('wait_duration')->avg('wait_duration');
        $avgServePeriod = (clone $baseQuery)->whereNotNull('serve_duration')->avg('serve_duration');

        // 1. REKAPITULASI BERDASARKAN JENIS LAYANAN
        $allLayanans = Layanan::orderBy('nama_layanan')->get();
        $layananBreakdown = [];
        foreach ($allLayanans as $layanan) {
            $layananQuery = (clone $baseQuery)->where('layanan_id', $layanan->id);
            $totalLayanan = (clone $layananQuery)->count();
            $completedLayanan = (clone $layananQuery)->where('status', 'completed')->count();
            $skippedLayanan = (clone $layananQuery)->where('status', 'skipped')->count();
            $avgWaitLayanan = (clone $layananQuery)->whereNotNull('wait_duration')->avg('wait_duration');
            $avgServeLayanan = (clone $layananQuery)->whereNotNull('serve_duration')->avg('serve_duration');

            $share = $totalPeriod > 0 ? round(($totalLayanan / $totalPeriod) * 100, 1) : 0;

            if ($totalLayanan > 0 || ! $selectedLayananId) {
                $layananBreakdown[] = [
                    'layanan' => $layanan,
                    'total' => $totalLayanan,
                    'completed' => $completedLayanan,
                    'skipped' => $skippedLayanan,
                    'share' => $share,
                    'avg_wait_formatted' => Queue::formatSeconds($avgWaitLayanan ? (int) $avgWaitLayanan : null),
                    'avg_serve_formatted' => Queue::formatSeconds($avgServeLayanan ? (int) $avgServeLayanan : null),
                ];
            }
        }
        usort($layananBreakdown, fn ($a, $b) => $b['total'] <=> $a['total']);

        // 2. REKAPITULASI BERDASARKAN MEJA LOKET
        $allMejas = Meja::with('user')->orderBy('nomor_meja')->get();
        $mejaBreakdown = [];
        foreach ($allMejas as $meja) {
            $mejaQuery = (clone $baseQuery)->where('meja_id', $meja->id);
            $totalMeja = (clone $mejaQuery)->count();
            $completedMeja = (clone $mejaQuery)->where('status', 'completed')->count();
            $skippedMeja = (clone $mejaQuery)->where('status', 'skipped')->count();
            $avgWaitMeja = (clone $mejaQuery)->whereNotNull('wait_duration')->avg('wait_duration');
            $avgServeMeja = (clone $mejaQuery)->whereNotNull('serve_duration')->avg('serve_duration');

            $shareMeja = $totalPeriod > 0 ? round(($totalMeja / $totalPeriod) * 100, 1) : 0;

            if ($totalMeja > 0 || ! $selectedMejaId) {
                $mejaBreakdown[] = [
                    'meja' => $meja,
                    'operator_name' => $meja->user?->name ?? 'Belum Ditautkan',
                    'total' => $totalMeja,
                    'completed' => $completedMeja,
                    'skipped' => $skippedMeja,
                    'share' => $shareMeja,
                    'avg_wait_formatted' => Queue::formatSeconds($avgWaitMeja ? (int) $avgWaitMeja : null),
                    'avg_serve_formatted' => Queue::formatSeconds($avgServeMeja ? (int) $avgServeMeja : null),
                ];
            }
        }
        usort($mejaBreakdown, fn ($a, $b) => $b['total'] <=> $a['total']);

        // 3. REKAPITULASI KINERJA MASING-MASING PETUGAS OPERATOR
        $allOperators = User::where('role', 'operator')->with('meja')->get();
        $operatorPerformance = [];
        foreach ($allOperators as $operator) {
            $opQuery = (clone $baseQuery)->where('operator_id', $operator->id);
            $totalHandled = (clone $opQuery)->count();
            $completed = (clone $opQuery)->where('status', 'completed')->count();
            $skipped = (clone $opQuery)->where('status', 'skipped')->count();
            $avgWait = (clone $opQuery)->whereNotNull('wait_duration')->avg('wait_duration');
            $avgServe = (clone $opQuery)->whereNotNull('serve_duration')->avg('serve_duration');
            $completionRate = $totalHandled > 0 ? round(($completed / $totalHandled) * 100, 1) : 0;

            if ($totalHandled > 0 || ! $selectedOperatorId) {
                $operatorPerformance[] = [
                    'operator' => $operator,
                    'meja' => $operator->meja?->nama_meja ?? '-',
                    'total_handled' => $totalHandled,
                    'completed' => $completed,
                    'skipped' => $skipped,
                    'completion_rate' => $completionRate,
                    'avg_wait_formatted' => Queue::formatSeconds($avgWait ? (int) $avgWait : null),
                    'avg_serve_formatted' => Queue::formatSeconds($avgServe ? (int) $avgServe : null),
                ];
            }
        }
        usort($operatorPerformance, fn ($a, $b) => $b['completed'] <=> $a['completed']);

        return [
            'baseQuery' => $baseQuery,
            'periode' => $periode,
            'periodeLabel' => $periodeLabel,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedLayananId' => $selectedLayananId,
            'selectedMejaId' => $selectedMejaId,
            'selectedOperatorId' => $selectedOperatorId,
            'selectedStatus' => $selectedStatus,
            'totalPeriod' => $totalPeriod,
            'completedPeriod' => $completedPeriod,
            'skippedPeriod' => $skippedPeriod,
            'waitingPeriod' => $waitingPeriod,
            'avgWaitFormatted' => Queue::formatSeconds($avgWaitPeriod ? (int) $avgWaitPeriod : null),
            'avgServeFormatted' => Queue::formatSeconds($avgServePeriod ? (int) $avgServePeriod : null),
            'layananBreakdown' => $layananBreakdown,
            'mejaBreakdown' => $mejaBreakdown,
            'operatorPerformance' => $operatorPerformance,
            'allLayanans' => $allLayanans,
            'allMejas' => $allMejas,
            'allOperators' => $allOperators,
        ];
    }
}
