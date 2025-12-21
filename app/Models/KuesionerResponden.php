<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class KuesionerResponden extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'group_id',
        'rmi_period_id',
        'verified_at',
        'verification_token',
        'approved_at',
        'approved_by',
    ];

    public $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function rmiPeriod()
    {
        return $this->belongsTo(RMIPeriod::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved()
    {
        return $this->approved_at !== null;
    }

    public function approve($approverId)
    {
        $this->approved_at = now();
        $this->approved_by = $approverId;
        $this->save();
    }

    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    public function markAsVerified()
    {
        $this->verified_at = now();
        $this->save();
    }

    public function sendVerificationEmail()
    {
        $verificationUrl = route('kuesioner-publik.verify', ['token' => $this->rmiPeriod->token, 'email' => $this->email, 'verification_token' => $this->verification_token]);
        Mail::send('emails.verification', ['token' => $this->verification_token, 'email' => $this->email, 'verificationUrl' => $verificationUrl], function ($message) {
            $message->to($this->email)->subject('Verifikasi Email untuk 
            Pengisian Kuesioner');
        });
    }
}
