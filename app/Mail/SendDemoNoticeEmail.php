<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDemoNoticeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name, $last_name, $email, $role)
    {
       $this->first_name = $first_name;
       $this->last_name = $last_name;
       $this->email = $email;
       $this->role = $role;
       $this->url = config('app.url') . '/eduadmin/edit/school/' . config('app.demo.school');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('A demo account has been created')
        ->view('email.demonotice')->with([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role,
            'url' => $this->url
        ]);
    }
}
