<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAccountEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $url, $demo, $note, $first_name = '')
    {
       $this->first_name = $first_name;
       $this->email = $email;
       $this->url = $url;
       $this->note = $note;
       $this->demo = $demo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->demo ? 'Demo Login Information' : 'Login Information';
        return $this->subject($subject)
        ->view('email.send_account')->with([
            'first_name' => $this->first_name,
            'email' => $this->email,
            'url' => $this->url,
            'note' => $this->note,
            'demo' => $this->demo
        ]);
    }
}
