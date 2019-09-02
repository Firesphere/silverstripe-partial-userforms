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
use SilverStripe\Forms\HiddenField;
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
        $controller = parent::create($record);
        $controller->doInit();

        if (
            $controller->data()->PasswordProtected &&
            $request->getSession()->get(PasswordForm::PASSWORD_SESSION_KEY) !== $partial->ID
        ) {
            return $this->redirect('verify');
        }

        // Set the session after init and check if the last session has expired
        // or another submission has started
        $sessionID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        if (!$sessionID || $sessionID !==  $partial->ID) {
            $request->getSession()->set(PartialSubmissionController::SESSION_KEY, $partial->ID);
        }

        $form = $controller->Form();
        $form->loadDataFrom($partial->getFields());
        $this->populateData($form, $partial);

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
     * Add partial submission and set the uploaded filenames as right title of the file fields
     *
     * @param Form $form
     * @param PartialFormSubmission $partial
     */
    protected function populateData($form, $partial)
    {
        $fields = $form->Fields();
        // Add partial submission ID
        $fields->push(
            HiddenField::create(
                'PartialID',
                null,
                $partial->ID
            )
        );

        // Populate files
        $uploads = $partial->PartialUploads()->filter([
            'UploadedFileID:not'=> null
        ]);

        if (!$uploads->exists()) {
            return;
        }

        foreach ($uploads as $upload) {
            $fields->dataFieldByName($upload->Name)
                ->setRightTitle(
                    sprintf(
                        'Uploaded: %s (Attach a new file to replace the uploaded file)',
                        $upload->UploadedFile()->Name
                    )
                );
        }
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
        $partial = PartialFormSubmission::validateKeyToken($key, $token);
        if ($partial === false) {
            return $this->httpError(404);
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
