<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCodesEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($code, $url, $demo, $note = '', $first_name = '')
    {
       $this->code = $code;
       $this->url = $url;
       $this->note = $note;
       $this->first_name = $first_name;
       $this->demo = $demo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Activation Code')
        ->view('email.sendcodes')->with([
            'activation_code' => $this->code,
            'url' => $this->url,
            'note' => $this->note,
            'first_name' => $this->first_name,
            'demo' => $this->demo
        ]);
    }
}
