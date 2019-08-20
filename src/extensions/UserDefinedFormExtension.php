<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class UserDefinedFormExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedForm|UserDefinedFormExtension $owner
 * @property boolean $ExportPartialSubmissions
 * @property boolean $PasswordProtected
 * @method DataList|PartialFormSubmission[] PartialSubmissions()
 */
class UserDefinedFormExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'ExportPartialSubmissions' => 'Boolean(true)',
        'PasswordProtected'        => 'Boolean(false)'
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'PartialSubmissions' => PartialFormSubmission::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        /** @var GridField $submissionField */
        $submissionField = $fields->dataFieldByName('Submissions');
        $list = $submissionField->getList()->exclude(['ClassName' => PartialFormSubmission::class]);
        $submissionField->setList($list);

        $fields->removeByName('PartialSubmissions');
        /** @var GridFieldConfig_RecordEditor $gridfieldConfig */
        $gridfieldConfig = GridFieldConfig_RecordEditor::create();
        $gridfieldConfig->removeComponentsByType(GridFieldAddNewButton::class);

        // We need to manually add the tab
        $fields->addFieldToTab(
            'Root',
            Tab::create('PartialSubmissions', _t(__CLASS__ . '.PartialSubmission', 'Partial submissions'))
        );

        $fields->addFieldToTab(
            'Root.PartialSubmissions',
            GridField::create(
                'PartialSubmissions',
                _t(__CLASS__ . '.PartialSubmission', 'Partial submissions'),
                $this->owner->PartialSubmissions(),
                $gridfieldConfig
            )
        );

        $fields->insertBefore(
            'DisableSaveSubmissions',
            $pwdCheckbox = CheckboxField::create(
                'PasswordProtected',
                _t(__CLASS__ . 'PasswordProtected', 'Password protect resuming partial submissions')
            )
        );
        $pwdDescription = _t(
            __CLASS__ . '.PasswordProtectDescription',
            'When resuming a partial submission, require the user to enter a password'
        );
        $pwdCheckbox->setDescription($pwdDescription);

        $fields->insertAfter(
            'DisableSaveSubmissions',
            $partialCheckbox = CheckboxField::create(
                'ExportPartialSubmissions',
                _t(__CLASS__ . '.partialCheckboxLabel', 'Send partial submissions')
            )
        );
        $description = _t(
            __CLASS__ . '.partialCheckboxDescription',
            'The configuration and global export configuration can be set in the site Settings'
        );
        $partialCheckbox->setDescription($description);
    }
}
