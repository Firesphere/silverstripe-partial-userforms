<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Page;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Control\UserDefinedFormController;

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
     * Partial form
     *
     * @param HTTPRequest $request
     * @return DBHTMLText|void
     * @throws HTTPResponse_Exception
     * @throws Exception
     */
    public function partial(HTTPRequest $request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');
        if (!$key || !$token) {
            return $this->httpError(404);
        }

        $partial = PartialFormSubmission::validateKeyToken($key, $token);
        if ($partial === false) {
            return $this->httpError(404);
        }

        // Set data record and load the form
        $record = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);
        $controller = parent::create($record);
        $controller->doInit();

        // Set the session if the last session has expired or another submission has started
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
}
