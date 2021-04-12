<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendServerMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($server_message, $server_file = '', $server_trace = '') {
        $this->server_message = $server_message;
        $this->server_file = $server_file;
        $this->server_trace = $server_trace;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Edu Server Message')
            ->view('email.server_message')->with([
                'server_message' => $this->server_message,
                'server_file' => $this->server_file,
                'server_trace' => $this->server_trace
        ]);
    }
}
