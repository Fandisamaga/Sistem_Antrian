<?php

namespace App\Http\Controllers;

use App\Models\Queue;

class DisplayController extends Controller
{
    public function index()
    {
        return view('display.index');
    }

    public function latest()
    {
        $queue = Queue::with('meja')
            ->where('status', 'called')
            ->latest('updated_at')
            ->first();

        return response()->json($queue);
    }
}
