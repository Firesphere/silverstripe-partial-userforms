<?php

namespace Firesphere\PartialUserforms\Forms;

use Firesphere\PartialUserforms\Controllers\PartialUserFormVerifyController;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Validator;

class PasswordForm extends Form
{
    public const PASSWORD_SESSION_KEY = 'PartialFormSession';

    /**
     * PasswordForm constructor.
     * @param PartialUserFormVerifyController|null $controller
     * @param string $name
     * @param FieldList|null $fields
     * @param FieldList|null $actions
     * @param Validator|null $validator
     */
    public function __construct(
        PartialUserFormVerifyController $controller = null,
        $name = self::DEFAULT_NAME,
        FieldList $fields = null,
        FieldList $actions = null,
        Validator $validator = null
    ) {
        if (!$fields) {
            $fields = $this->getFields();
        }
        if (!$actions) {
            $actions = $this->getActions();
        }
        if (!$validator) {
            $validator = $this->getFormValidator();
        }
        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->setFormAction(sprintf('/verify/%s', $name));

        // Add the userform class to the form, so it's can be styled similar to the actual userforms more easily
        $this->addExtraClass('userform');
    }

    /**
     * @return FieldList
     */
    protected function getFields()
    {
        return FieldList::create([
            PasswordField::create('Password', _t(__CLASS__ . '.PasswordField', 'Password'))
        ]);
    }

    /**
     * @return FieldList
     */
    protected function getActions()
    {
        return FieldList::create([
            FormAction::create('doValidate', _t(__CLASS__ . '.Validate', 'Submit'))
        ]);
    }

    /**
     * @return RequiredFields
     */
    public function getFormValidator()
    {
        return RequiredFields::create(['Password']);
    }
}
