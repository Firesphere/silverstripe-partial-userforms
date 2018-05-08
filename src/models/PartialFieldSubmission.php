<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class PartialFieldSubmission extends SubmittedFormField
{
    private static $table_name = 'PartialFieldSubmission';

    private static $has_one = [
        'SubmittedForm' => PartialFormSubmission::class,
    ];
}
