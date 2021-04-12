<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClassEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($student_code, $teacher_code, $class_name)
    {
       $this->student_code = $student_code;
       $this->teacher_code = $teacher_code;
       $this->class_name = $class_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Class: ' . $this->class_name)
        ->view('email.classemail')->with([
            'teacher_code' => $this->teacher_code,
            'student_code' => $this->student_code,
            'class' => $this->class_name,
            'host' => \Config::get('app.url')
        ]);
    }
}
