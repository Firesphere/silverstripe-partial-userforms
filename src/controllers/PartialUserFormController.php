<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Page;
use SilverStripe\Control\HTTPRequest;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class PartialUserFormController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialUserFormController extends UserDefinedFormController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$Key/$Token' => 'partial',
    ];
    /**
     * @var array
     */
    private static $allowed_actions = [
        'partial',
    ];
    /**
     * @var PartialFormSubmission
     */
    protected $partialFormSubmission;

    /**
     * Partial form
     *
     * @param HTTPRequest $request
     * @return HTTPResponse|DBHTMLText|void
     * @throws HTTPResponse_Exception
     * @throws Exception
     */
    public function partial(HTTPRequest $request)
    {
        /** @var PartialFormSubmission $partial */
        $partial = $this->validateToken($request);
        $record = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);
        /** @var self $controller */
        $controller = self::create($record);
        $controller->doInit();
        $controller->dataRecord = $record;
        if ($controller->data()->PasswordProtected &&
            $request->getSession()->get(PasswordForm::PASSWORD_SESSION_KEY) !== $partial->ID
        ) {
            return $this->redirect('verify');
        }

        /** @var Form $form */
        $form = $controller->Form();
        $fields = $partial->PartialFields()->map('Name', 'Value')->toArray();
        $form->loadDataFrom($fields);

        // Copied from {@link UserDefinedFormController}
        if ($controller->Content && $form && !$controller->config()->disable_form_content_shortcode) {
            $hasLocation = stristr($controller->Content, '$UserDefinedForm');
            if ($hasLocation) {
                /** @see Requirements_Backend::escapeReplacement */
                $formEscapedForRegex = addcslashes($form->forTemplate(), '\\$');
                $content = preg_replace(
                    '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                    $formEscapedForRegex,
                    $controller->Content
                );

                return $controller->customise([
                    'Content'     => DBField::create_field('HTMLText', $content),
                    'Form'        => '',
                    'PartialLink' => $partial->getPartialLink()
                ])->renderWith([static::class, Page::class]);
            }
        }

        return $controller->customise([
            'Content'     => DBField::create_field('HTMLText', $controller->Content),
            'Form'        => $form,
            'PartialLink' => $partial->getPartialLink()
        ])->renderWith([static::class, Page::class]);
    }

    /**
     * A little abstraction to be more readable
     *
     * @param HTTPRequest $request
     * @return PartialFormSubmission|void
     * @throws HTTPResponse_Exception
     */
    public function validateToken($request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');
        if (!$key || !$token) {
            return $this->httpError(404);
        }

        /** @var PartialFormSubmission $partial */
        $partial = PartialFormSubmission::get()->find('Token', $token);
        if (!$token ||
            !$partial ||
            !$partial->UserDefinedFormID ||
            !hash_equals($partial->generateKey($token), $key)
        ) {
            return $this->httpError(404);
        }

        $sessionKey = PartialSubmissionController::SESSION_KEY;
        // Set the session if the last session has expired
        if (!$request->getSession()->get($sessionKey)) {
            $request->getSession()->set($sessionKey, $partial->ID);
        }

        return $partial;
    }

    /**
     * @return PartialFormSubmission
     */
    public function getPartialFormSubmission(): PartialFormSubmission
    {
        return $this->partialFormSubmission;
    }

    /**
     * @param PartialFormSubmission $partialFormSubmission
     */
    public function setPartialFormSubmission(PartialFormSubmission $partialFormSubmission): void
    {
        $this->partialFormSubmission = $partialFormSubmission;
    }
}
