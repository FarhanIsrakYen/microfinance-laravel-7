<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;

class Unique implements Rule
{
    protected $table;
    protected $excepetRowId;
    protected $excepetColum;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table, $excepetRowId = null, $excepetColum = 'id')
    {
        $this->table = $table;
        $this->excepetColum = $excepetColum;
        $this->excepetRowId = $excepetRowId;
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
        $isExists = DB::table($this->table)
        ->where([
            ['is_delete', 0],
            [$attribute, $value],
        ]);
        if ($this->excepetRowId != null) {
            $isExists->where($this->excepetColum , '!=', $this->excepetRowId);
        }

        return !$isExists->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
