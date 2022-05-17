<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class PhasedBudgetReminder extends Mailable
{
    use Queueable, SerializesModels;

	/**
	 * @var string $unitName
	 */
	public $unitName;

	/**
	 * @var string $budgetDate
	 */
	public $budgetDate;
	
    /**
     * Create a new message instance.
     * 
     * @param string $unitName
     * @param string $budgetDate
     *
     * @return void
     */
    public function __construct($unitName, $budgetDate)
    {
        $this->unitName = $unitName;
        $this->budgetDate = $budgetDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
	    return $this->subject("Phased Budget reminder for {$this->unitName}")->view('emails.phased-budget-reminder');
    }
}
