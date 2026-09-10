<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            ->limit(150)
            ->get();

        return view('admin.reports.print', array_merge($data, [
            'queues' => $queues,
        ]));
    }

    private function buildReportData(Request $request): array
    {
        $dateFrom = $request->input('date_from', Carbon::today()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());
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

        if ($selectedMejaId) {
            $baseQuery->where('meja_id', $selectedMejaId);
        }

        if ($selectedOperatorId) {
            $baseQuery->where('operator_id', $selectedOperatorId);
        }

        if ($selectedStatus) {
            $baseQuery->where('status', $selectedStatus);
        }

        // Statistik Ringkasan Periode
        $totalPeriod = (clone $baseQuery)->count();
        $completedPeriod = (clone $baseQuery)->where('status', 'completed')->count();
        $skippedPeriod = (clone $baseQuery)->where('status', 'skipped')->count();
        $avgWaitPeriod = (clone $baseQuery)->whereNotNull('wait_duration')->avg('wait_duration');
        $avgServePeriod = (clone $baseQuery)->whereNotNull('serve_duration')->avg('serve_duration');

        // Kinerja Masing-Masing Operator
        $operators = User::where('role', 'operator')->with('meja')->get();
        $operatorPerformance = [];

        foreach ($operators as $operator) {
            $opQuery = Queue::where('operator_id', $operator->id)
                ->where(function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('queue_date', [$dateFrom, $dateTo])
                        ->orWhereBetween('created_at', [
                            Carbon::parse($dateFrom)->startOfDay(),
                            Carbon::parse($dateTo)->endOfDay(),
                        ]);
                });

            $totalHandled = (clone $opQuery)->count();
            $completed = (clone $opQuery)->where('status', 'completed')->count();
            $skipped = (clone $opQuery)->where('status', 'skipped')->count();

            $avgWait = (clone $opQuery)->whereNotNull('wait_duration')->avg('wait_duration');
            $avgServe = (clone $opQuery)->whereNotNull('serve_duration')->avg('serve_duration');

            $completionRate = $totalHandled > 0 ? round(($completed / $totalHandled) * 100, 1) : 0;

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

        // Urutkan operator berdasarkan jumlah antrean selesai terbanyak
        usort($operatorPerformance, fn ($a, $b) => $b['completed'] <=> $a['completed']);

        $allMejas = Meja::orderBy('nomor_meja')->get();

        return [
            'baseQuery' => $baseQuery,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedMejaId' => $selectedMejaId,
            'selectedOperatorId' => $selectedOperatorId,
            'selectedStatus' => $selectedStatus,
            'totalPeriod' => $totalPeriod,
            'completedPeriod' => $completedPeriod,
            'skippedPeriod' => $skippedPeriod,
            'avgWaitFormatted' => Queue::formatSeconds($avgWaitPeriod ? (int) $avgWaitPeriod : null),
            'avgServeFormatted' => Queue::formatSeconds($avgServePeriod ? (int) $avgServePeriod : null),
            'operatorPerformance' => $operatorPerformance,
            'allMejas' => $allMejas,
            'allOperators' => $operators,
        ];
    }
}

