<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf; 

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tutor;
    public $details;
    public $totalEarnings;
    public $deductions;
    public $periodStart;
    public $periodEnd;

    public function __construct($tutor, $details, $totalEarnings, $deductions, $periodStart, $periodEnd)
    {
        $this->tutor = $tutor;
        $this->details = $details;
        $this->totalEarnings = $totalEarnings;
        $this->deductions = $deductions;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function build()
    {
        $pdf = Pdf::loadView('payroll.partials.payslip-pdf', [
            'tutor' => $this->tutor,
            'details' => $this->details,
            'totalEarnings' => $this->totalEarnings,
            'deductions' => $this->deductions,
            'periodStart' => $this->periodStart,
            'periodEnd' => $this->periodEnd,
        ]);

        return $this->subject('Payslip for ' . $this->tutor->full_name)
                    ->view('emails.payslip') // optional HTML body
                    ->attachData($pdf->output(), 'payslip.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
