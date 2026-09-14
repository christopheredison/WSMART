<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class RMIPeriodDocument extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'rmi_period_documents';

    protected $fillable = [
        'rmi_period_id',
        'file_name',
        'file_path',
        'mimetype',
        'description',
    ];

    protected $casts = [
        'file_name' => 'string',
        'file_path' => 'string',
        'mimetype' => 'string',
    ];

    public function rmiPeriod()
    {
        return $this->belongsTo(RMIPeriod::class, 'rmi_period_id');
    }
}
