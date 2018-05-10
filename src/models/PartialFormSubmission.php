<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFormSubmission
 *
 * @property boolean $IsSend
 * @property int $UserDefinedFormID
 * @method \SilverStripe\ORM\DataObject UserDefinedForm()
 * @method \SilverStripe\ORM\DataList|\Firesphere\PartialUserforms\Models\PartialFieldSubmission[] PartialFields()
 */
class PartialFormSubmission extends SubmittedForm
{
    private static $table_name = 'PartialFormSubmission';

    private static $db = [
        'IsSend' => 'Boolean(false)'
    ];

    private static $has_one = [
        'UserDefinedForm' => DataObject::class
    ];

    private static $has_many = [
        'PartialFields' => PartialFieldSubmission::class
    ];

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }
}
