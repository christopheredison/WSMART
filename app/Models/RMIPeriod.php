<?php

namespace App\Models;

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
        'start_date',
        'end_date',
        'status',  // 1 : Dalam Proses, 2 : Selesai
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

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
        return $this->hasOne(PenilaianCapaianKinerja::class, 'rmi_period_id');
    }
}
