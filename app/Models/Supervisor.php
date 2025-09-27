<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $primaryKey = 'supID';
    public $incrementing = false; // Primary key is not auto-incrementing
    protected $keyType = 'string'; // Primary key is string type
    
    protected $fillable = [
        'supID', // Now this stores the formatted ID directly (OGS-S0001)
        'accID',
        'sfname',
        'smname',
        'slname',
        'semail',
        'sconNum'
    ];

    // Automatically generate formatted ID when creating new supervisors
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supervisor) {
            if (empty($supervisor->supID)) {
                $supervisor->supID = $supervisor->generateFormattedId();
            }
        });
    }

    /**
     * Generate formatted ID for new supervisors
     */
    public function generateFormattedId(): string
    {
        // Get the last supervisor by extracting the number from supID
        $lastSupervisor = self::orderByRaw('CAST(SUBSTRING(supID, 6) AS UNSIGNED) DESC')->first();
        if ($lastSupervisor && preg_match('/OGS-S(\d+)/', $lastSupervisor->supID, $matches)) {
            $nextId = ((int) $matches[1]) + 1;
        } else {
            $nextId = 1;
        }
        return 'OGS-S' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the full name of the supervisor
     */
    public function getFullNameAttribute()
    {
        return trim(($this->sfname ?? '') . ' ' . ($this->smname ?? '') . ' ' . ($this->slname ?? ''));
    }

    /**
     * Search scope for supervisors
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // If searching for formatted ID pattern, extract numeric part
                if (preg_match('/^OGS-S(\d+)$/', $search, $matches)) {
                    $numericId = (int) $matches[1];
                    $q->where('supID', $numericId);
                } else {
                    // Regular search on other fields
                    $q->where('sfname', 'LIKE', "%{$search}%")
                      ->orWhere('smname', 'LIKE', "%{$search}%")
                      ->orWhere('slname', 'LIKE', "%{$search}%")
                      ->orWhere('semail', 'LIKE', "%{$search}%");
                }
            });
        }
        return $query;
    }
}
