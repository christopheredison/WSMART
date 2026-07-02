<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\User;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'valid');
        $today = now()->toDateString();

        $query = Unit::with(['unitType', 'parent'])->withTrashed();

        if ($status === 'valid') {
            // Tampilkan yang masih valid: valid_to null atau >= hari ini, dan valid_from null atau <= hari ini
            $query->where(function ($q) use ($today) {
                $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today);
            })->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            });
        } elseif ($status === 'invalid') {
            // Tampilkan yang sudah tidak valid: valid_to terisi dan < hari ini
            $query->whereNotNull('valid_to')->whereDate('valid_to', '<', $today);
        } // status 'all' menampilkan semua

        $unit = $query->get();
        return view('master.unit.index', compact('unit', 'status'));
    }

    public function create()
    {
        $unitType = UnitType::pluck('name','id');
        $parent = Unit::pluck('name','id');
        return view('master.unit.create', compact('unitType','parent'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit_type_id' => 'required',
            // 'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Unit::create([
            'name' => $request->name,
            'unit_type_id' => $request->unit_type_id,
            'unit_api_id' => $request->unit_api_id,
            'parent_id' => $request->parent_id ?? 0
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit created successfully!');
    }

    public function edit(Unit $unit)
    {
        $unitType = UnitType::pluck('name','id');
        $parent = Unit::pluck('name','id');
        return view('master.unit.edit', compact('unit','unitType','parent'));
    }

    public function update(Request $request, Unit $unit)
    {
        // Normalisasi tanggal ke format Y-m-d agar validasi konsisten
        $normalizedFrom = $this->normalizeDate($request->valid_from);
        $normalizedTo = $this->normalizeDate($request->valid_to);
        $request->merge([
            'valid_from' => $normalizedFrom,
            'valid_to' => $normalizedTo,
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit_type_id' => 'required',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            // 'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Gunakan nilai yang telah dinormalisasi
        $validFrom = $request->valid_from ?: null;
        $validTo = $request->valid_to ?: null;

        $unit->update([
            'name' => $request->name,
            'unit_type_id' => $request->unit_type_id,
            'parent_id' => $request->parent_id ?? 0,
            'unit_api_id' => $request->unit_api_id,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit updated successfully!');
    }

    /**
     * Normalisasi tanggal dari berbagai format ke Y-m-d.
     */
    private function normalizeDate($value)
    {
        if (!$value) return null;
        try {
            if (str_contains($value, '/')) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }
            // Contoh: "30 November 2025" atau langsung "Y-m-d"
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit deleted successfully!');
    }

    public function restore($id)
    {
        $unit = Unit::withTrashed()->find($id);

        if ($unit) {
            $unit->restore();
            return redirect()->route('unit.index')->with('success', 'Unit restored successfully!');
        }

        return redirect()->route('unit.index')->with('error', 'Unit not found!');
    }

    public function sync()
    {
        Unit::sync();

        return response()->json(['message' => 'Unit synced successfully']);
    }

    public function logs(Request $request)
    {
        $event = $request->query('event');
        $unitId = $request->query('unit_id');

        $units = Unit::query()
            ->withTrashed()
            ->orderBy('name')
            ->get(['id', 'name']);

        $audits = Audit::query()
            ->with(['user', 'auditable'])
            ->where('auditable_type', Unit::class)
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
            ->when($event, function ($query) use ($event) {
                $query->where('event', $event);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('master.unit.logs', compact('units', 'audits', 'event', 'unitId'));
    }

    public function logsByUnit(Request $request, Unit $unit)
    {
        $request->merge(['unit_id' => $unit->id]);

        return $this->logs($request);
    }
}
