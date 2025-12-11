<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class SecurityQuestion extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'question',
        'answer_hash',
    ];

    protected $hidden = [
        'answer_hash',
    ];

    /**
     * Set the answer and hash it automatically
     */
    public function setAnswerAttribute($value)
    {
        $this->attributes['answer_hash'] = Hash::make(strtolower(trim($value)));
    }

    /**
     * Verify if the provided answer matches the stored hash
     */
    public function verifyAnswer($answer)
    {
        $normalizedAnswer = strtolower(trim($answer));
        
        // If answer_hash is empty or null, verification should fail
        if (empty($this->answer_hash)) {
            \Log::warning('Security question verification failed: empty answer_hash', [
                'question_id' => $this->id,
                'question' => $this->question
            ]);
            return false;
        }
        
        $result = Hash::check($normalizedAnswer, $this->answer_hash);
        
        // Log failed attempts for security
        if (!$result) {
            \Log::info('Security question verification failed', [
                'question_id' => $this->id,
                'user_type' => $this->user_type,
                'user_id' => $this->user_id
            ]);
        }
        
        return $result;
    }

    /**
     * Get security question for a specific user
     */
    public static function getForUser($userType, $userId)
    {
        return self::where('user_type', $userType)
                  ->where('user_id', $userId)
                  ->first();
    }

    /**
     * Get all security questions for a specific user
     */
    public static function getAllForUser($userType, $userId)
    {
        return self::where('user_type', $userType)
                  ->where('user_id', $userId)
                  ->get();
    }

    /**
     * Create or update security question for a user
     */
    public static function setForUser($userType, $userId, $question, $answer)
    {
        return self::updateOrCreate(
            [
                'user_type' => $userType,
                'user_id' => $userId,
            ],
            [
                'question' => $question,
                'answer_hash' => Hash::make(strtolower(trim($answer))),
            ]
        );
    }

    /**
     * Add a new security question for a user (allows multiple questions)
     */
    public static function addForUser($userType, $userId, $question, $answer)
    {
        return self::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'question' => $question,
            'answer_hash' => Hash::make(strtolower(trim($answer))),
        ]);
    }

    /**
     * Check if user has a security question set up
     */
    public static function hasSecurityQuestion($userType, $userId)
    {
        return self::where('user_type', $userType)
                  ->where('user_id', $userId)
                  ->exists();
    }
}