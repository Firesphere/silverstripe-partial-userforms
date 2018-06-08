<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFieldSubmission
 *
 * @property int $SubmittedFormID
 * @method \Firesphere\PartialUserforms\Models\PartialFormSubmission SubmittedForm()
 */
class PartialFieldSubmission extends SubmittedFormField
{
    private static $table_name = 'PartialFieldSubmission';

    private static $has_one = [
        'SubmittedForm' => PartialFormSubmission::class,
    ];

    /**
     * @param Member $member
     * @param array $context
     * @return boolean|string
     */
    public function canCreate($member = null, $context = [])
    {
        return $this->SubmittedForm()->canCreate();
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canView($member = null)
    {
        return $this->SubmittedForm()->canView();
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canEdit($member = null)
    {
        return $this->SubmittedForm()->canEdit();
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canDelete($member = null)
    {
        return $this->SubmittedForm()->canDelete();
    }
}
