<?php

namespace Firesphere\PartialUserforms\Controllers;

use Page;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\UserForms\Control\UserDefinedFormController;

/**
 * Class PartialUserFormController
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
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|void
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function partial(HTTPRequest $request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');

        $partial = PartialFormSubmission::get()->find('Token', $token);
        if (!$token || !$partial || !$partial->UserDefinedFormID) {
            return $this->httpError(404);
        }

        // TODO: Recognize visitor with the password
        if ($partial->generateKey($token) === $key) {
            // Set the session if the last session has expired
            if (!$request->getSession()->get(PartialSubmissionController::SESSION_KEY)) {
                $request->getSession()->set(PartialSubmissionController::SESSION_KEY, $partial->ID);
            }

            // Set data record and load the form
            $this->dataRecord = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);
            $this->setFailover($this->dataRecord);

            $form = $this->Form();
            $fields = $partial->PartialFields()->map('Name', 'Value')->toArray();
            $form->loadDataFrom($fields);

            // Copied from {@link UserDefinedFormController}
            if ($this->Content && $form && !$this->config()->disable_form_content_shortcode) {
                $hasLocation = stristr($this->Content, '$UserDefinedForm');
                if ($hasLocation) {
                    /** @see Requirements_Backend::escapeReplacement */
                    $formEscapedForRegex = addcslashes($form->forTemplate(), '\\$');
                    $content = preg_replace(
                        '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                        $formEscapedForRegex,
                        $this->Content
                    );

                    return $this->customise([
                        'Content' => DBField::create_field('HTMLText', $content),
                        'Form' => '',
                        'PartialLink' => $partial->getPartialLink()
                    ])->renderWith([static::class, Page::class]);
                }
            }

            return $this->customise([
                'Content' => DBField::create_field('HTMLText', $this->Content),
                'Form' => $form,
                'PartialLink' => $partial->getPartialLink()
            ])->renderWith([static::class, Page::class]);
        } else {
            return $this->httpError(404);
        }
    }
}
