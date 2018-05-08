<?php

namespace Firesphere\PartialUserforms\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\DataExtension;

class UserDefinedFormExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        /** @var GridFieldConfig_RelationEditor $gridfieldConfig */
        $gridfieldConfig = GridFieldConfig_RelationEditor::create();
        $gridfieldConfig->removeComponentsByType(GridFieldAddNewButton::class);

        $fields->addFieldToTab('Root.PartialSubmissions',
            GridField::create(''));
    }
}