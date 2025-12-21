<?php

namespace App\Models;

use App\Supports\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RMIPeriod extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rmi_periods';

    protected $fillable = [
        'year',
        'score_rmi',
        'score_rmi_desc',
        'kinerja',
        'kpmr',
        'peringkat_komposit_risiko',
        'nilai_konversi',
        'score_aspek_kinerja',
        'final_score_rmi',
        'adjusment_score',
        'start_date',
        'end_date',
        'status',  // 1 : Dalam Proses, 2 : Selesai
        'penilaian',
        'tipe_penilaian', // 1 : Eksternal, 2 : Internal
        'tahun_dinilai',
        'penilai_external',
        'score_rmi_external',
        'score_rmi_external_desc',
        'kinerja_external',
        'kpmr_external',
        'peringkat_komposit_risiko_external',
        'nilai_konversi_external',
        'score_aspek_kinerja_external',
        'adjusment_score_external',
        'final_score_rmi_external',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public static function getByToken(string $token): ?RMIPeriod
    {
        $id = Helper::decrypt($token);
        return self::find($id);
    }

    public function periodQuestions()
    {
        return $this->hasMany(PeriodQuestion::class, 'period_id');
    }

    public function userSurveys()
    {
        return $this->hasMany(UserSurvey::class, 'period_id');
    }

    public function userSurvey()
    {
        return $this->hasOne(UserSurvey::class, 'period_id');
    }

    public function penilaianCapaianKinerja()
    {
        return $this->hasOne(PenilaianCapaianKinerja::class, 'rmi_period_id')->latestOfMany();
    }

    public function documents()
    {
        return $this->hasMany(RMIPeriodDocument::class, 'rmi_period_id');
    }

    public function getTokenAttribute()
    {
        return Helper::encrypt($this->id);
    }

    public function isActive(): bool
    {
        $today = now()->startOfDay();
        return $this->start_date->lte($today) && $this->end_date->gte($today);
    }
}
