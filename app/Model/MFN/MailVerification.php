<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class MailVerification extends Model
{
    protected $table = 'mfn_mail_verification';
    public $timestamps = false;

    protected $fillable = [
        'memberId', 'email', 'eToken', 'isVerified',
    ];
}
