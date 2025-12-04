<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable class for sending notification when an applicant fails or needs rescheduling
 * 
 * This email will be sent to the applicant's personal email when they:
 * - Miss their interview/demo (no answer)
 * - Need to reschedule
 * - Are declined
 * - Are not recommended
 */
class ApplicantFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $applicantEmail;
    public $phase;
    public $failReason;
    public $newSchedule;
    public $interviewer;
    public $notes;

    /**
     * Create a new message instance.
     *
     * @param string $applicantName Full name of the applicant
     * @param string $applicantEmail Applicant's email address
     * @param string $phase Current phase (screening, demo, onboarding)
     * @param string $failReason Reason for failure (no_answer, re_schedule, declined, not_recommended)
     * @param string|null $newSchedule New schedule time (for reschedule cases)
     * @param string|null $interviewer Name of the supervisor
     * @param string|null $notes Additional notes or feedback
     */
    public function __construct(
        string $applicantName,
        string $applicantEmail,
        string $phase,
        string $failReason,
        ?string $newSchedule = null,
        ?string $interviewer = null,
        ?string $notes = null
    ) {
        $this->applicantName = $applicantName;
        $this->applicantEmail = $applicantEmail;
        $this->phase = $phase;
        $this->failReason = $failReason;
        $this->newSchedule = $newSchedule;
        $this->interviewer = $interviewer;
        $this->notes = $notes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->failReason) {
            'no_answer' => 'Missed Interview - OGS Connect',
            're_schedule' => 'Interview Rescheduled - OGS Connect',
            'declined' => 'Application Status Update - OGS Connect',
            'not_recommended' => 'Application Status Update - OGS Connect',
            default => 'Application Update - OGS Connect',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.applicant-failed',
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
