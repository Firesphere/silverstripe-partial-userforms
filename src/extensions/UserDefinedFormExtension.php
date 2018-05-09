<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension
 *
 * @property \DNADesign\ElementalUserForms\Model\ElementForm|\SilverStripe\UserForms\Model\UserDefinedForm|\Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension $owner
 * @property boolean $ExportPartialSubmissions
 * @method \SilverStripe\ORM\DataList|\Firesphere\PartialUserforms\Models\PartialFormSubmission[] PartialSubmissions()
 */
class UserDefinedFormExtension extends DataExtension
{
    private static $db = [
        'ExportPartialSubmissions' => 'Boolean(true)',
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
        /** @var GridFieldConfig_RelationEditor $gridfieldConfig */
        $gridfieldConfig = GridFieldConfig_RelationEditor::create();
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
                $this->owner->PartialSubmissions()
            )
        );

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
