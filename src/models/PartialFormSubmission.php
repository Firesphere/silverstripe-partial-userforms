<?php

namespace Firesphere\PartialUserforms\Models;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\RandomGenerator;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFormSubmission
 *
 * @property boolean $IsSend
 * @property string $TokenSalt
 * @property string $Token
 * @property int $UserDefinedFormID
 * @method DataObject UserDefinedForm()
 * @method DataList|PartialFieldSubmission[] PartialFields()
 * @method DataList|PartialFileFieldSubmission[] PartialUploads()
 */
class PartialFormSubmission extends SubmittedForm
{
    private static $table_name = 'PartialFormSubmission';

    private static $db = [
        'IsSend'    => 'Boolean(false)',
        'TokenSalt' => 'Varchar(16)',
        'Token'     => 'Varchar(16)',
    ];

    private static $has_one = [
        'UserDefinedForm' => DataObject::class
    ];

    private static $has_many = [
        'PartialFields'  => PartialFieldSubmission::class,
        'PartialUploads' => PartialFileFieldSubmission::class
    ];

    private static $cascade_deletes = [
        'PartialFields'
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'ID'          => 'ID',
        'PartialLink' => 'Link',
        'Created'     => 'Created',
        'LastEdited'  => 'Last Edited',
    ];

    public function getCMSFields()
    {
        /** @var FieldList $fields */
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'Values',
            'IsSend',
            'PartialFields',
            'TokenSalt',
            'Token',
            'UserDefinedFormID',
            'Submitter',
            'PartialUploads'
        ]);

        $partialFields = $this->PartialFields();
        $fileFields = $this->PartialUploads();
        $list = ArrayList::create();
        $list->merge($partialFields);
        $list->merge($fileFields);

        $partialFields = GridField::create(
            'PartialFields',
            _t(static::class . '.PARTIALFIELDS', 'Partial fields'),
            $list->sort('Created', 'ASC')
        );

        $exportColumns = [
            'Title'       => 'Title',
            'ExportValue' => 'Value'
        ];

        $config = new GridFieldConfig();
        $config->addComponent(new GridFieldDataColumns());
        $config->addComponent(new GridFieldButtonRow('after'));
        $config->addComponent(new GridFieldExportButton('buttons-after-left', $exportColumns));
        $config->addComponent(new GridFieldPrintButton('buttons-after-left'));
        $partialFields->setConfig($config);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('ReadonlyPartialLink', 'Partial Link', $this->getPartialLink()),
                $partialFields
            ]
        );

        return $fields;
    }

    public function getParent()
    {
        return $this->UserDefinedForm();
    }

    /**
     * @param Member $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param Member
     *
     * @return boolean|string
     */
    public function canView($member = null)
    {
        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canView($member);
        }

        return parent::canView($member);
    }

    /**
     * @param Member
     *
     * @return boolean|string
     */
    public function canEdit($member = null)
    {
        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canEdit($member);
        }

        return parent::canEdit($member);
    }

    /**
     * @param Member
     *
     * @return boolean|string
     */
    public function canDelete($member = null)
    {
        if ($this->UserDefinedForm()) {
            return $this->UserDefinedForm()->canDelete($member);
        }

        return parent::canDelete($member);
    }

    /**
     * Get the share link of the form
     *
     * @return string
     * @throws Exception
     */
    public function getPartialLink()
    {
        if (!$this->isInDB()) {
            return '(none)';
        }

        $token = $this->getPartialToken();

        return Controller::join_links(
            Director::absoluteBaseURL(),
            'partial',
            $this->generateKey($token),
            $token
        );
    }

    /**
     * Get the unique token for the share link
     *
     * @return bool|string|null
     * @throws Exception
     */
    protected function getPartialToken()
    {
        if (!$this->TokenSalt) {
            $this->TokenSalt = $this->generateToken();
            $this->Token = $this->generateToken();
            $this->write();
        }

        return $this->Token;
    }

    /**
     * Generate a new token
     *
     * @return bool|string
     * @throws Exception
     */
    protected function generateToken()
    {
        $generator = new RandomGenerator();

        return substr($generator->randomToken('sha256'), 0, 16);
    }

    /**
     * Generate a key based on the share token salt
     *
     * @param string $token
     * @return string|bool
     */
    public function generateKey($token)
    {
        return hash_pbkdf2('sha256', $token, $this->TokenSalt, 1000, 16);
    }
}
