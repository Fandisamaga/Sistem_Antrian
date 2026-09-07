<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use App\Models\Queue;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $meja = $request->user()->meja;
        abort_unless($meja, 403, 'Operator belum ditautkan ke meja.');
        return view('operator.index', ['meja' => $meja, 'queues' => $meja->queues()->whereIn('status', ['waiting', 'called'])->oldest()->get()]);
    }
    public function update(Request $request, Queue $queue)
    {
        $user = $request->user();
        abort_unless($user->role === 'admin' || $queue->meja_id === $user->meja_id, 403);
        $action = $request->validate(['action' => ['required', 'in:call,replay,skip,complete']])['action'];
        if ($action === 'call') { $queue->update(['status' => 'called']); event(new QueueCalled($queue->fresh('meja'))); }
        if ($action === 'replay') { event(new QueueCalled($queue->load('meja'))); }
        if ($action === 'skip') $queue->update(['status' => 'skipped']);
        if ($action === 'complete') $queue->update(['status' => 'completed']);
        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}
