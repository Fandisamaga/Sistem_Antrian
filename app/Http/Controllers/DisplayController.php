<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;

class DisplayController extends Controller
{
    public function index()
    {
        return view('display.index', [
            'waitingQueues' => $this->waitingQueues(),
            'latestQueue' => $this->latestCalledQueue(),
        ]);
    }

    public function latest()
    {
        return $this->latestCalledQueue();
    }

    public function state()
    {
        return response()->json([
            'latest' => $this->latestCalledQueue(),
            'waiting' => $this->waitingQueues(),
        ]);
    }

    private function waitingQueues()
    {
        $today = Carbon::today()->toDateString();

        return Queue::with(['meja', 'layanan'])
            ->where('status', 'waiting')
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->orWhere('queue_date', $today);
            })
            ->oldest('id')
            ->get();
    }

    private function latestCalledQueue()
    {
        $today = Carbon::today()->toDateString();

        return Queue::with(['meja', 'layanan'])
            ->where('status', 'called')
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                    ->orWhere('queue_date', $today);
            })
            ->latest('called_at')
            ->latest('updated_at')
            ->first();
    }
}
