<?php

namespace App\Mail;

use App\OperationsScorecard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OperationScorecard extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var OperationsScorecard
     */
    public $scoreCard;
    /**
     * @var array
     */
    public $unitInfo;
    /**
     * @var Collection
     */
    public $attachedFiles;

    /**
     * Create a new message instance.
     *
     * @param OperationsScorecard $scoreCard
     * @param array               $unitInfo
     * @param Collection          $attachmedFiles
     *
     * @return void
     */
    public function __construct($scoreCard, $unitInfo, $attachedFiles)
    {
        $this->scoreCard = $scoreCard;
        $this->unitInfo = $unitInfo;
        $this->attachedFiles = $attachedFiles;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject("Operations Scorecard")->view('emails.operations-scorecard');

        foreach ($this->attachedFiles as $attachedFile) {
            $email->attach($attachedFile);
        }

        return $email;
    }
}
