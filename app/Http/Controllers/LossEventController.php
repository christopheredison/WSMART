<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LossEvent;
use App\Models\LossEventChild;
use Auth;
use App\Models\Periode;
use App\Models\User;
use App\Models\Unit;

class LossEventController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $lossEvent = LossEvent::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->get();
        return view('loss-events.index',compact('lossEvent'));
    }

    public function create()
    {
        return view('loss-events.create');
    }

    public function store(Request $request)
    {
        // Simpan data loss_event
        $loss_event = LossEvent::create($request->all());

        // Simpan data loss_event_children
        if ($request->has('rekomendasi_perbaikan')) {
            foreach ($request->rekomendasi_perbaikan as $perbaikan) {
                $loss_event->lossEventChildren()->create(['rekomendasi_perbaikan' => $perbaikan]);
            }
        }

        return redirect()->route('loss-events.index');
    }

    public function edit(LossEvent $loss_event)
    {
        return view('loss-events.edit', compact('loss_event'));
    }

    public function update(Request $request, LossEvent $loss_event)
    {
        // Update data loss_event
        $loss_event->update($request->all());

        // Update data loss_event_children
        if ($request->has('rekomendasi_perbaikan')) {
            foreach ($request->rekomendasi_perbaikan as $index => $perbaikan) {
                $loss_event->lossEventChildren[$index]->update(['rekomendasi_perbaikan' => $perbaikan]);
            }
        }

        return redirect()->route('loss-events.index');
    }

    public function destroy($loss_event)
    {
        $lossEventId = LossEvent::where('id',$loss_event)->first();
        $lossEventId->delete();

        return redirect()->route('loss-events.index')->with('success', 'Loss Event deleted successfully!');
    }
}
