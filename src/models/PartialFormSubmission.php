<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;

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

    public function getCMSFields()
    {
        /** @var FieldList $fields */
        $fields = parent::getCMSFields();
        $fields->removeByName(['Values', 'IsSend']);
        $config = new GridFieldConfig();
        $config->addComponent(new GridFieldDataColumns());
        $config->addComponent(new GridFieldPrintButton());

        $fields->dataFieldByName('PartialFields')->setConfig($config);

        return $fields;
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canCreate($member, $context);
        }

        if (!$this->Parent()) {
            $this->ParentID = $this->UserDefinedFormID;
        }

        return parent::canCreate($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);

        if ($extended !== null) {
            return $extended;
        }

        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canView($member);
        }

        return parent::canView($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return false;
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);

        if ($extended !== null) {
            return $extended;
        }

        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canDelete($member);
        }

        return parent::canDelete($member);
    }
}
