<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Services\QueueService;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    public function index() { return view('cs.index', ['mejas' => Meja::orderBy('nama_meja')->get()]); }
    public function store(Request $request, Meja $meja, QueueService $queues)
    {
        $queue = $queues->createFor($meja);
        if ($request->expectsJson()) return response()->json(['queue' => $queue, 'meja' => $meja->nama_meja], 201);
        return back()->with('queue', $queue->queue_number);
    }
}
