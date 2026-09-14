<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskImpactDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_risk_analisa_id',
        'uraian',
        'volume',
        'satuan',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'volume' => 'decimal:4',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function projectRiskAnalisa()
    {
        return $this->belongsTo(ProjectRiskAnalisa::class, 'project_risk_analisa_id');
    }

    public function formattedVolume(): string
    {
        $formatted = number_format((float) $this->volume, 4, ',', '.');

        if (!str_contains($formatted, ',')) {
            return $formatted;
        }

        return rtrim(rtrim($formatted, '0'), ',');
    }
}

