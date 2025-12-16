<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $name;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $name)
    {
        $this->otp = $otp;
        $this->name = $name;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your OGS Connect password reset code')
            ->view('emails.reset-otp')
            ->with([
                'otp' => $this->otp,
                'name' => $this->name,
            ]);
    }
}
