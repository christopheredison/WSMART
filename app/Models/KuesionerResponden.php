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
        'approval_status',
        'validated_at',
        'validated_by',
        'rejection_notes',
    ];

    public $casts = [
        'verified_at' => 'datetime',
        'validated_at' => 'datetime',
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
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function approve($approverId)
    {
        $this->approval_status = 'approved';
        $this->validated_at = now();
        $this->validated_by = $approverId;
        $this->save();

        $link = route('kuesioner-publik.fill', ['token' => $this->rmiPeriod->token, 'email' => $this->email, 'verification_token' => $this->verification_token]);

        Mail::send('emails.approval', ['name' => $this->name, 'email' => $this->email, 'link' => $link], function ($message) {
            $message->to($this->email)->subject('Pendaftaran Kuesioner Disetujui');
        });
    }

    public function reject($approverId, $notes = null)
    {
        $this->approval_status = 'rejected';
        $this->validated_at = now();
        $this->validated_by = $approverId;
        $this->rejection_notes = $notes;
        $this->save();

        Mail::send('emails.rejection', ['name' => $this->name, 'email' => $this->email, 'notes' => $notes], function ($message) {
            $message->to($this->email)->subject('Pendaftaran Kuesioner Ditolak');
        });
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
