<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class PartialFormSubmission extends SubmittedForm
{
    private static $table_name = 'PartialFormSubmission';

    private static $has_many = [
        'PartialFields' => PartialFieldSubmission::class,
    ];
}
