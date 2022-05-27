<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DatabaseDump extends Mailable
{
    use Queueable, SerializesModels;

    private $dumpFilePath;

    /**
     * Create a new message instance.
     *
     * @param string $dumpFilePath
     *
     * @return void
     */
    public function __construct($dumpFilePath)
    {
        $this->dumpFilePath = $dumpFilePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Database dump')->attach($this->dumpFilePath);
    }
}
