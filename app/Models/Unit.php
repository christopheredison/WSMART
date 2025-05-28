<?php

namespace App\Models;

use App\Supports\ApiHC;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'units';

    protected $fillable = [
        'unit_api_id',
        'unit_type_id',
        'name',
        'parent_id'
    ];

    public const UNIT_TYPE_DIVISION = 1;
    public const UNIT_TYPE_DEPARTMENT = 2;
    public const UNIT_TYPE_PROJECT = 3;

    public static function sync() {
        $apiHC = new ApiHC();
        $units = $apiHC->apiRequest('GET', '/', [
            'method' => 'get_assign_ccplace',
            'page' => 1,
            'limit' => 999999,
            'key' => 'GZrmL5TH',
        ]);

        if (!($units['data'] ?? [])) {
            throw new \Exception('Unit List API return empty data. Message: ' . ($units['message'] ?? 'No message'));
        }

        $unitTypes = [
            'Divisi' => self::UNIT_TYPE_DIVISION,
            'Department' => self::UNIT_TYPE_DEPARTMENT,
            'Project' => self::UNIT_TYPE_PROJECT,
        ];
        
        foreach ($units['data'] as $unit) {
            $unit = Unit::updateOrCreate(
                ['unit_api_id' => $unit['unit_id']],
                [
                    'name' => $unit['unit_deskripsi'],
                    'unit_type_id' => $unitTypes[$unit['cost_center_type']] ?? 2,
                    'parent_id' => 0,
                ]
            );
        }
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children()
    {
        return $this->HasMany(Unit::class, 'parent_id');
    }
}
