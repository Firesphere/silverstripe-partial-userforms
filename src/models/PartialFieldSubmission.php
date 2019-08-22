<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class PartialFieldSubmission
 *
 * @property int $SubmittedFormID
 * @method PartialFormSubmission SubmittedForm()
 * @package Firesphere\PartialUserforms\Models
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
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canView($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canView($member);
        }

        return parent::canView($member);
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canEdit($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canEdit($member);
        }

        return parent::canEdit($member);
    }

    /**
     * @param Member $member
     *
     * @return boolean|string
     */
    public function canDelete($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canDelete($member);
        }

        return parent::canDelete($member);
    }
}
