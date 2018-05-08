<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFormSubmission
 *
 * @method \SilverStripe\ORM\DataList|\Firesphere\PartialUserforms\Models\PartialFieldSubmission[] PartialFields()
 */
class PartialFormSubmission extends SubmittedForm
{
    private static $table_name = 'PartialFormSubmission';

    private static $has_many = [
        'PartialFields' => PartialFieldSubmission::class
    ];
}
