<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;

class EmailSender
{
    public $template;
    public $data;
    public $from;
    public $name_sender;
    public $to;
    public $subject;
    public $name;
    public $file;
    public $res;

    /**
     * send Email Custom Template
     *
     * @return string
     */
    public function sendEmail()
    {
        try {
            $template = $this->template;
            $data = (array) $this->data;
            $name_sender = $this->name_sender;
            $from = $this->from;
            $to = $this->to;
            $subject = $this->subject;
            $name = (!empty($this->name) ? $this->name : 'Member');

            Mail::send($template, $data, function ($email) use ($from, $name_sender, $to, $subject, $name) {
                $email->priority(1);
                $email->to($to, $name)->subject($subject);
                $email->from($from, $name_sender);
            });

            return $this->res = "send";
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    /**
     * send Email Custom Template with file
     *
     * @return string
     */
    public function sendEmailWithFile()
    {
        try {
            $template = $this->template;
            $data = (array) $this->data;
            $name_sender = $this->name_sender;
            $from = $this->from;
            $to = $this->to;
            $subject = $this->subject;
            $name = (!empty($this->name) ? $this->name : 'Indominer');
            $file = $this->file;

            Mail::send($template, $data, function ($email) use ($from, $name_sender, $to, $subject, $name, $file) {
                $email->priority(1);
                $email->to($to, $name)->subject($subject);
                $email->from($from, $name_sender);
                $email->attach($file);
            });

            return $this->res = "send";
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
}
