<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFieldSubmission
 *
 * @property int $UserDefinedFormID
 * @method \Firesphere\PartialUserforms\Models\PartialFormSubmission SubmittedForm()
 */
class PartialFieldSubmission extends SubmittedFormField
{
    private static $table_name = 'PartialFieldSubmission';

    private static $has_one = [
        'SubmittedForm' => PartialFormSubmission::class,
    ];
}
