<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($expiring, $payment_due, $host) {
        $this->expiring = $expiring;
        $this->payment_due = $payment_due;
        $this->host = $host;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Inventionland Institute Admin Update')
        ->view('email.daily_admin')->with([
            'expiring' => $this->expiring,
            'payment_due' => $this->payment_due,
            'host' => $this->host
        ]);
    }
}
