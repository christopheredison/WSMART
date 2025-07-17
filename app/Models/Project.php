<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'project_name',
        'project_divisi_id',
        'project_sektor_id',
        'type',
        'project_status',
        'meta',
        'kode_tender',
        'nama_tender',
        'tender_status',
        'owner',
        'owner_category',
        'sumber_dana',
        'project_location_id',
        'project_type_id',
        'jenis_kontrak',
        'cara_pembayaran',
        'scope_pekerjaan',
        'nk',
        'nk_ppn',
        'masa_pelaksanaan_start',
        'masa_pelaksanaan_end',
        'masa_pelaksanaan', // virtual attribute ! NOT A TYPO ! DO NOT REMOVE
        'rapt',
        'rapt_persentase',
        'rapk',
        'rapk_persentase',
        'rapk_0_10_rp',
        'rapk_0_10_persen',
        'rapk_30_50_rp',
        'rapk_30_50_persen',
        'rapk_70_90_rp',
        'rapk_70_90_persen',
        'rapk_100_rp',
        'rapk_100_persen',
        'batas_nilai',
    ];

    protected $casts = [
        'meta' => 'array',
        'nk' => 'float',
        'nk_ppn' => 'float',
        'masa_pelaksanaan_start' => 'date:Y-m-d',
        'masa_pelaksanaan_end' => 'date:Y-m-d',
        'rapt' => 'float',
        'rapt_persentase' => 'float',
        'rapk' => 'float',
        'rapk_persentase' => 'float',
        'rapk_0_10_rp' => 'float',
        'rapk_0_10_persen' => 'float',
        'rapk_30_50_rp' => 'float',
        'rapk_30_50_persen' => 'float',
        'rapk_70_90_rp' => 'float',
        'rapk_70_90_persen' => 'float',
        'rapk_100_rp' => 'float',
        'rapk_100_persen' => 'float',
        'batas_nilai' => 'integer',
    ];

    // for autofill project
    public const TYPE_NO_RKB_RKN = 1;
    public const TYPE_HAS_RKB_RKN = 2;
    // for other purpose
    public const TYPE_TENDER = 1;
    public const TYPE_OPERASIONAL = 2;

    public const TENDER_STATUS_MENANG = 1;
    public const TENDER_STATUS_KALAH = 2;
    public const TENDER_STATUS_ON_GOING = 3;

    public const OWNER_CATEGORY_PEMERINTAH_PUSAT = 1;
    public const OWNER_CATEGORY_PEMERINTAH_DAERAH = 2;
    public const OWNER_CATEGORY_BUMN = 3;
    public const OWNER_CATEGORY_BUMD = 4;
    public const OWNER_CATEGORY_SWASTA = 5;

    public const SUMBER_DANA_APBD = 1;
    public const SUMBER_DANA_APBN = 2;
    public const SUMBER_DANA_BUMN = 3;
    public const SUMBER_DANA_BUMD = 4;
    public const SUMBER_DANA_LOAN = 5;
    public const SUMBER_DANA_SWASTA = 6;

    public const JENIS_KONTRAK_LUMPSUM = 1;
    public const JENIS_KONTRAK_UNIT_PRICE = 2;
    public const JENIS_KONTRAK_GABUNGAN = 3;
    public const JENIS_KONTRAK_TURN_KEY = 4;

    public const CARA_PEMBAYARAN_MONTHLY = 1;
    public const CARA_PEMBAYARAN_TERMIN = 2;
    public const CARA_PEMBAYARAN_PREFINANCING = 3;

    public const SCOPE_PEKERJAAN_DESAIN_BUILD = 1;
    public const SCOPE_PEKERJAAN_CONSTRUCTION_ONLY = 2;
    public const SCOPE_PEKERJAAN_EPC = 3;

    public $appends = [
        'masa_pelaksanaan',
    ];

    public function projectDivisi()
    {
        return $this->belongsTo(ProjectDivisi::class);
    }

    public function projectSektor()
    {
        return $this->belongsTo(ProjectSektor::class);
    }

    public function riskLimit() : Attribute {
        return Attribute::make(function () {
            return ($this->meta['rkn'] ?? 0) * (2/100);
        })->shouldCache();
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function projectLocation()
    {
        return $this->belongsTo(ProjectLocation::class, 'project_location_id');
    }

    public function getMasaPelaksanaanAttribute()
    {
        return ($this->masa_pelaksanaan_start?->format('d/m/Y')) . ' - ' . ($this->masa_pelaksanaan_end?->format('d/m/Y'));
    }

    public function getFormattedMasaPelaksanaanAttribute()
    {
        $startDate = $this->masa_pelaksanaan_start?->format('d/m/Y');
        $endDate = $this->masa_pelaksanaan_end?->format('d/m/Y');

        if ($startDate === $endDate) {
            return $startDate;
        }

        return $startDate . ' - ' . $endDate;
    }

    public function setMasaPelaksanaanAttribute($value)
    {
        $dates = explode(' - ', $value);
        $dates = array_map(function ($date) {
            return trim($date, '-');
        }, $dates);
        //$this->attributes['masa_pelaksanaan_start'] = $dates[0] ? \Carbon\Carbon::createFromFormat('d/m/Y', $dates[0]) : null;
        //$this->attributes['masa_pelaksanaan_end'] = ($dates[1] ?? null) ? \Carbon\Carbon::createFromFormat('d/m/Y', $dates[1]) : $this->attributes['masa_pelaksanaan_start'];
    }

    public function projectPeriodeList()
    {
        return $this->hasOne(ProjectPeriodeList::class)->whereNull('periode_id');
    }

    protected function displayMasaPelaksanaanStart(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->masa_pelaksanaan_start?->format('d/m/Y')
        );
    }

    protected function displayMasaPelaksanaanEnd(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->masa_pelaksanaan_end?->format('d/m/Y')
        );
    }
}
