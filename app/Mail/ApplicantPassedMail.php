<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable class for sending notification when an applicant passes a phase
 * 
 * This email will be sent to the applicant's personal email when they successfully
 * pass any phase in the hiring process (screening, demo, onboarding)
 */
class ApplicantPassedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $applicantEmail;
    public $phase;
    public $nextPhase;
    public $nextSchedule;
    public $interviewer;
    public $notes;
    public $companyEmail;
    public $password;

    /**
     * Create a new message instance.
     *
     * @param string $applicantName Full name of the applicant
     * @param string $applicantEmail Applicant's email address
     * @param string $phase Current phase that was passed (screening, demo, training)
     * @param string|null $nextPhase Next phase they're moving to
     * @param string|null $nextSchedule Schedule for next phase (if applicable)
     * @param string|null $interviewer Name of the supervisor who assessed them
     * @param string|null $notes Additional notes or feedback
     * @param string|null $companyEmail Company email (only for onboarding pass)
     * @param string|null $password Temporary password (only for onboarding pass)
     */
    public function __construct(
        string $applicantName,
        string $applicantEmail,
        string $phase,
        ?string $nextPhase = null,
        ?string $nextSchedule = null,
        ?string $interviewer = null,
        ?string $notes = null,
        ?string $companyEmail = null,
        ?string $password = null
    ) {
        $this->applicantName = $applicantName;
        $this->applicantEmail = $applicantEmail;
        $this->phase = $phase;
        $this->nextPhase = $nextPhase;
        $this->nextSchedule = $nextSchedule;
        $this->interviewer = $interviewer;
        $this->notes = $notes;
        $this->companyEmail = $companyEmail;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $phaseTitle = ucfirst($this->phase);
        return new Envelope(
            subject: "Congratulations! You've Passed the {$phaseTitle} Phase - OGS Connect",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.applicant-passed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
