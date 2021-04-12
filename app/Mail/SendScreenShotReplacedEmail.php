<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendScreenShotReplacedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name, $assignment_name, $assignment_screenshot) {
        $this->first_name = $first_name;
        $this->assignment_name = $assignment_name;
        $this->assignment_screenshot = $assignment_screenshot;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Assignment\'s screenshot has been replaced')
            ->view('email.screenshotreplaced')->with([
                'first_name' => $this->first_name,
                'assignment_name' => $this->assignment_name,
                'assignment_screenshot' => $this->assignment_screenshot,
        ]);
    }
}
