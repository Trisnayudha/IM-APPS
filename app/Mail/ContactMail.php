<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $category;
    public $pesan;
    public $subject;

    public function __construct($user, $category, $subject, $pesan)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->category = $category;
        $this->pesan = $pesan;
    }

    public function build()
    {

        return $this->from(env('EMAIL_SENDER'), env('EMAIL_NAME'))
            ->subject('Inquiry about Contact Information ' . $this->subject)
            ->view('email.contact_us', [
                'name' => $this->user->name,
                'company' => $this->user->company_name,
                'job_title' => $this->user->job_title,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'category' => $this->category,
                'pesan' => $this->pesan,

            ]);
    }
}
