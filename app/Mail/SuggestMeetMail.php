<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuggestMeetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $name;
    public $users_name;
    public $category_name;
    public $message;

    public function __construct($name, $users_name, $category_name, $message)
    {
        $this->name = $name;
        $this->users_name = $users_name;
        $this->category_name = $category_name;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('EMAIL_SENDER'), env('EMAIL_NAME'))
            ->subject($this->users_name . ' asking for your company information')
            ->view('email.suggest_meeting', [
                'name' => $this->name,
                'sender' => $this->users_name,
                'category' => $this->category_name,
                'message_text' => $this->message
            ]);
    }
}
