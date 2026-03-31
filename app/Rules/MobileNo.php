<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;

class MobileNo implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $mobileNumberLength = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content)->mobileNoLength;

        $mobileNumberLength = (int) $mobileNumberLength - 3;

        if ($mobileNumberLength > 0) {
            return preg_match("/^01[3456789][0-9]{".$mobileNumberLength."}\b/", $value);
        }

        return preg_match("/^01[3456789][0-9]{8}\b/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a Valid Mobile Number.';
    }
}
