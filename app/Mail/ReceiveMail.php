<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiveMail extends Mailable
{
    use Queueable, SerializesModels;
    public $type;
    public $find;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $find)
    {
        $this->type = $type;
        $this->find = $find;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return  $this->from(env('EMAIL_SENDER'), env('EMAIL_NAME'))
            ->subject('Thank you for registering Indonesia Miner 2024')
            ->view('email.receive_benefit', [
                'name' => $this->find->name,
                'company' => $this->find->company_name,
                'job_title' => $this->find->job_title,
                'phone' => $this->find->phone,
                'email' => $this->find->email,
                'type' => $this->type,
            ]);
    }
}
