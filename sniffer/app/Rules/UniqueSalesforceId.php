<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Contact;
use App\Notifications\Slack;

class UniqueSalesforceId implements Rule
{
    protected $lId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($lId)
    {
        $this->lId = $lId;
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
        $contactByLId = Contact::where('linkedin_id', $this->lId)->first();

        $slackTitle = '*Sniffer* [_UniqueSalesforceId Rule Fail_]  ';
        if (empty($contactByLId)) {
            // New
            if (empty($value)) {
                return true;
            }

            $attachmentTitle = 'Trying to create a Contact';
            $attachmentContent = 'salesforce_id taken `' . $value . '`';
        } else {
            // Update
            if (empty($value)) {
                return true;
            }
            $attachmentTitle = 'Trying to update a Contact (LinkedinId = ' . $this->lId . ')';
        }

        // (new Contact)->notify(new Slack($slackTitle, $attachmentTitle, $attachmentContent));
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The SalesforceId is invalid.';
    }
}
