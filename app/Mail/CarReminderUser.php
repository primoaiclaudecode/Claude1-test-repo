<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class CarReminderUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string $emailSubject
     */
    public $emailSubject;
    /**
     * @var Collection $emailData
     */
    public $emailData;

    /**
     * Create a new message instance.
     *
     * @param string     $emailSubject
     * @param Collection $emailData
     *
     * @return void
     */
    public function __construct($emailSubject, $emailData)
    {
        $this->emailData = $emailData;
        $this->emailSubject = $emailSubject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.car-reminder-user');
    }
}
