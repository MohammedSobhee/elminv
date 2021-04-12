<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $email, $name, $school, $bodyMessage)
    {
        $this->subject = $subject;
        $this->email = $email;
        $this->name = $name;
        $this->school = $school;
        $this->bodyMessage = $bodyMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Support: ' . $this->subject)
        ->view('email.support')->with([
            'subject' => $this->subject,
            'email' => $this->email,
            'name' => $this->name,
            'school' => $this->school,
            'bodyMessage' => $this->bodyMessage
        ]);
    }
}
