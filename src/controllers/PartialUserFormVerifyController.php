<?php


namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Page;
use PageController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class \Firesphere\PartialUserforms\Controllers\PartialUserFormVerifyController
 *
 */
class PartialUserFormVerifyController extends PageController
{
    public const PASSWORD_KEY = 'FormPassword';

    /**
     * @var array
     */
    private static $allowed_actions = [
        'getForm'
    ];
    /**
     * @var PartialFormSubmission
     */
    protected $partialFormSubmission;

    /**
     * @var PasswordForm
     */
    protected $form;

    /**
     * @return PartialUserFormVerifyController|void
     * @throws HTTPResponse_Exception
     */
    public function init()
    {
        parent::init();
        $session = $this->getRequest()->getSession();
        $sessionKey = PartialSubmissionController::SESSION_KEY;
        // Set the session if the last session has expired
        if (!$session->get($sessionKey)) {
            return $this->httpError(404);
        }

        /** @var PartialFormSubmission $partial */
        $partial = PartialFormSubmission::get()->byID($session->get($sessionKey));

        $this->setPartialFormSubmission($partial);
        // Set data record and load the form
        /** @var UserDefinedForm dataRecord */
        $this->dataRecord = Page::create();
    }

    /**
     * @return PasswordForm
     */
    public function getForm()
    {
        return PasswordForm::create($this, __FUNCTION__);
    }


    /**
     * @param array $data
     * @param PasswordForm $form
     * @return HTTPResponse
     * @throws Exception
     */
    public function doValidate($data, $form)
    {
        /** @var PartialFormSubmission $partial */
        $partial = $this->getPartialFormSubmission();

        $password = hash_pbkdf2('SHA256', $data['Password'], $partial->TokenSalt, 1000);
        if (!hash_equals($password, $partial->Password)) {
            $form->sessionError(
                _t(
                    PasswordForm::class . '.PASSWORDERROR',
                    'Password incorrect, please check your password and try again'
                )
            );

            return $this->redirectBack();
        }

        $request = $this->getRequest();
        $request->getSession()->set(PasswordForm::PASSWORD_SESSION_KEY, $partial->ID);
        $request->getSession()->set(self::PASSWORD_KEY, $data['Password']);

        return $this->redirect($partial->getPartialLink());
    }

    /**
     * @return PartialFormSubmission
     */
    public function getPartialFormSubmission()
    {
        return $this->partialFormSubmission;
    }

    /**
     * @param PartialFormSubmission $partialFormSubmission
     */
    public function setPartialFormSubmission($partialFormSubmission): void
    {
        $this->partialFormSubmission = $partialFormSubmission;
    }
}
