<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetupEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($teacher_code, $school_code, $school_name, $first_name)
    {
        $this->teacher_code = $teacher_code;
        $this->school_code = $school_code;
        $this->school_name = $school_name;
        $this->first_name = $first_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Activation Instructions for ' . $this->school_name)
        ->view('email.setup')->with([
            'teacher_code' => $this->teacher_code,
            'school_code' => $this->school_code,
            'school_name' => $this->school_name,
            'first_name' => $this->first_name,
            'host' => \Config::get('app.url')
        ]);
    }
}
