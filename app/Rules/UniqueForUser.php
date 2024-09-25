<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Customer;

class UniqueForUser implements Rule
{
    protected $field;
    protected $currentValue;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($field, $currentValue)
    {
        $this->field = $field;  // the field being validated (email or username)
        $this->currentValue = $currentValue; // the current user's value
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
        // if the new value is the same as the current, pass validation
        if ($value === $this->currentValue) {
            return true;
        }

          // Otherwise, check for uniqueness in the database
          return !Customer::where($this->field, $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is already taken.';
    }
}
