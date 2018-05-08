<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension
 *
 * @property \SilverStripe\UserForms\Model\UserDefinedForm|\Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension $owner
 * @method \SilverStripe\ORM\DataList|\Firesphere\PartialUserforms\Models\PartialFormSubmission[] PartialSubmissions()
 */
class UserDefinedFormExtension extends DataExtension
{
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
        /** @var GridFieldConfig_RelationEditor $gridfieldConfig */
        $gridfieldConfig = GridFieldConfig_RelationEditor::create();
        $gridfieldConfig->removeComponentsByType(GridFieldAddNewButton::class);

        // We need to manually add the tab
        $fields->addFieldToTab(
            'Root',
            Tab::create('PartialSubmissions', _t(__CLASS__ . 'PartialSubmission', 'Partial submissions'))
        );

        $fields->addFieldToTab(
            'Root.PartialSubmissions',
            GridField::create(
                'PartialSubmissions',
                _t(__CLASS__ . 'PartialSubmission', 'Partial submissions'),
                $this->owner->PartialSubmissions()
            )
        );
    }
}
