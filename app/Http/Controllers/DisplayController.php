<?php

namespace App\Http\Controllers;

use App\Models\Queue;

class DisplayController extends Controller
{
    public function index()
    {
        $calledQueues = $this->calledQueues();

        return view('display.index', [
            'calledQueues' => $calledQueues,
            'latestQueue' => $calledQueues->first(),
        ]);
    }

    public function latest()
    {
        return Queue::with('meja')
            ->where('status', 'called')
            ->latest('updated_at')
            ->first();
    }

    public function state()
    {
        $calledQueues = $this->calledQueues();

        return response()->json([
            'latest' => $calledQueues->first(),
            'called' => $calledQueues,
        ]);
    }

    private function calledQueues()
    {
        return Queue::with('meja')
            ->where('status', 'called')
            ->latest('updated_at')
            ->latest('id')
            ->get();
    }
}
