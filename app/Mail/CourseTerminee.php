<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Course;

class CourseTerminee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function build()
    {
        return $this->subject('Confirmation de réception de la livraison')
            ->view('emails.course-terminee-html')
            ->text('emails.course-terminee-text');
    }
}
