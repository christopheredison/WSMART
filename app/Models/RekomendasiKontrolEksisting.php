<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiKontrolEksisting extends Model
{
    use HasFactory;
    protected $table = 'rekomendasi_kontrol_eksistings';
    protected $fillable = ['rekomendasi_risiko_id', 'kontrol_eksisting'];
}