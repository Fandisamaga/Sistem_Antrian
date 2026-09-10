<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Meja;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $todayQueuesQuery = Queue::query()
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->orWhere('queue_date', $today);
            });

        $totalToday = (clone $todayQueuesQuery)->count();
        $completedToday = (clone $todayQueuesQuery)->where('status', 'completed')->count();
        $skippedToday = (clone $todayQueuesQuery)->where('status', 'skipped')->count();
        $waitingToday = (clone $todayQueuesQuery)->where('status', 'waiting')->count();

        $avgWaitSeconds = (clone $todayQueuesQuery)->whereNotNull('wait_duration')->avg('wait_duration');
        $avgServeSeconds = (clone $todayQueuesQuery)->whereNotNull('serve_duration')->avg('serve_duration');

        $activeOperatorsCount = (clone $todayQueuesQuery)
            ->whereNotNull('operator_id')
            ->distinct('operator_id')
            ->count('operator_id');

        $totalMejas = Meja::count();
        $totalLayanans = Layanan::where('is_active', true)->count();
        $totalOperators = User::where('role', 'operator')->count();

        $recentQueues = Queue::with(['meja', 'layanan', 'operator'])
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->orWhere('queue_date', $today);
            })
            ->latest('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalToday' => $totalToday,
            'completedToday' => $completedToday,
            'skippedToday' => $skippedToday,
            'waitingToday' => $waitingToday,
            'avgWait' => Queue::formatSeconds($avgWaitSeconds ? (int) $avgWaitSeconds : null),
            'avgServe' => Queue::formatSeconds($avgServeSeconds ? (int) $avgServeSeconds : null),
            'activeOperatorsCount' => $activeOperatorsCount,
            'totalMejas' => $totalMejas,
            'totalLayanans' => $totalLayanans,
            'totalOperators' => $totalOperators,
            'recentQueues' => $recentQueues,
        ]);
    }
}

