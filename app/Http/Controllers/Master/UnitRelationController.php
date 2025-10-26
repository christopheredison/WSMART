<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\UnitRelation;
use Illuminate\Support\Facades\Validator;

class UnitRelationController extends Controller
{
    public function index(Unit $unit)
    {
        $today = now()->toDateString();
        $relations = UnitRelation::with('relatedUnit')
            ->where('unit_id', $unit->id)
            ->get()
            ->map(function ($rel) {
                return [
                    'id' => $rel->id,
                    'related_unit_id' => $rel->related_unit_id,
                    'related_unit_name' => optional($rel->relatedUnit)->name,
                    'valid_to' => optional($rel->relatedUnit?->valid_to)->format('Y-m-d'),
                    'status' => $rel->relatedUnit?->status,
                ];
            });

        // Invalid units candidates: valid_to < today OR status == false
        $invalidUnits = Unit::query()
            ->where(function ($q) use ($today) {
                $q->whereNotNull('valid_to')->whereDate('valid_to', '<', $today);
            })
            ->orWhere('status', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                ];
            });

        return response()->json([
            'unit' => ['id' => $unit->id, 'name' => $unit->name],
            'relations' => $relations,
            'invalid_units' => $invalidUnits,
        ]);
    }

    public function store(Request $request, Unit $unit)
    {
        $validator = Validator::make($request->all(), [
            'related_unit_id' => 'required|exists:units,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $relatedUnitId = (int) $request->input('related_unit_id');

        // Prevent self relation
        if ($relatedUnitId === $unit->id) {
            return response()->json(['message' => 'Tidak boleh merelasikan unit ke dirinya sendiri'], 422);
        }

        // Upsert relation (avoid duplicate)
        $relation = UnitRelation::firstOrCreate([
            'unit_id' => $unit->id,
            'related_unit_id' => $relatedUnitId,
        ]);

        return response()->json([
            'message' => 'Relasi unit berhasil ditambahkan',
            'relation' => $relation,
        ]);
    }

    public function destroy(Unit $unit, UnitRelation $relation)
    {
        if ($relation->unit_id !== $unit->id) {
            return response()->json(['message' => 'Relasi tidak sesuai dengan unit'], 422);
        }
        $relation->delete();

        return response()->json(['message' => 'Relasi unit berhasil dihapus']);
    }
}