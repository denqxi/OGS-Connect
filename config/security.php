<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Login Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application, including login attempt limits and rate limiting.
    |
    */

    'login' => [
        /*
        |--------------------------------------------------------------------------
        | Maximum Login Attempts
        |--------------------------------------------------------------------------
        |
        | The maximum number of login attempts allowed before the user is
        | temporarily locked out. This helps prevent brute force attacks.
        |
        */
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 3),

        /*
        |--------------------------------------------------------------------------
        | Login Attempt Decay Minutes
        |--------------------------------------------------------------------------
        |
        | The number of minutes to wait before allowing another login attempt
        | after the maximum attempts have been reached.
        |
        */
        'decay_minutes' => env('LOGIN_DECAY_MINUTES', 15),

        /*
        |--------------------------------------------------------------------------
        | Progressive Lockout
        |--------------------------------------------------------------------------
        |
        | Enable progressive lockout that increases wait time with repeated failures
        | and offers password reset after multiple lockouts.
        |
        */
        'progressive_lockout' => env('LOGIN_PROGRESSIVE_LOCKOUT', true),
        
        /*
        |--------------------------------------------------------------------------
        | Password Reset After Lockouts
        |--------------------------------------------------------------------------
        |
        | Number of lockouts before forcing password reset
        |
        */
        'reset_after_lockouts' => env('LOGIN_RESET_AFTER_LOCKOUTS', 3),

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting Key
        |--------------------------------------------------------------------------
        |
        | The key used for rate limiting login attempts. This combines the
        | login ID and IP address to create a unique identifier.
        |
        */
        'throttle_key' => 'login_attempts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset Security
    |--------------------------------------------------------------------------
    |
    | Configuration for password reset security features.
    |
    */
    'password_reset' => [
        /*
        |--------------------------------------------------------------------------
        | Password Reset Token Expiry
        |--------------------------------------------------------------------------
        |
        | The number of minutes a password reset token remains valid.
        |
        */
        'token_expiry' => env('PASSWORD_RESET_TOKEN_EXPIRY', 60),

        /*
        |--------------------------------------------------------------------------
        | Password Reset Throttle
        |--------------------------------------------------------------------------
        |
        | The number of seconds to wait before allowing another password reset
        | request from the same user.
        |
        */
        'throttle_seconds' => env('PASSWORD_RESET_THROTTLE', 60),
    ],
];
