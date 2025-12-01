<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorWorkDetailApproval extends Model
{
    use HasFactory;

    protected $table = 'tutor_work_detail_approvals';

    protected $fillable = [
        'work_detail_id',
        'supervisor_id',
        'old_status',
        'new_status',
        'approved_at',
        'note',
    ];

    public function workDetail()
    {
        return $this->belongsTo(TutorWorkDetail::class, 'work_detail_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }
}
