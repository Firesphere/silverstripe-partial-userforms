<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFormSubmission
 *
 * @property int $UserDefinedFormID
 * @method \SilverStripe\UserForms\Model\UserDefinedForm UserDefinedForm()
 * @method \SilverStripe\ORM\DataList|\Firesphere\PartialUserforms\Models\PartialFieldSubmission[] PartialFields()
 */
class PartialFormSubmission extends SubmittedForm
{
    private static $table_name = 'PartialFormSubmission';

    private static $has_one = [
        'UserDefinedForm' => UserDefinedForm::class
    ];

    private static $has_many = [
        'PartialFields' => PartialFieldSubmission::class
    ];
}
