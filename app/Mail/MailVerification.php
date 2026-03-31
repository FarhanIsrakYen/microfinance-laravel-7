<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $memberId;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($memberId, $token)
    {
        $this->memberId = $memberId;
        $this->token = $token;
    }

    /** 
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = url('mfn/mail/verify/' . encrypt($this->memberId) . '/' . $this->token);

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject('Email Verification')
            ->markdown('MFN.Mail.mail_verification')
            ->with([
                'url' => $url
            ]);
    }
}
