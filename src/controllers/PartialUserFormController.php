<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Page;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
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

        /** @var PartialFormSubmission $partial */
        $partial = PartialFormSubmission::get()->find('Token', $token);
        if (!$partial ||
            !$partial->UserDefinedFormID ||
            !hash_equals($partial->generateKey($token), $key)
        ) {
            return $this->httpError(404);
        }

        // Set the session if the last session has expired
        if (!$request->getSession()->get(PartialSubmissionController::SESSION_KEY)) {
            $request->getSession()->set(PartialSubmissionController::SESSION_KEY, $partial->ID);
        }

        // TODO: Recognize visitor with the password
        // Set data record and load the form
$record = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);
$controller = parent::create($record);
$controller->doInit();
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
                    'Content'     => DBField::create_field('HTMLText', $content),
                    'Form'        => '',
                    'PartialLink' => $partial->getPartialLink()
                ])->renderWith([static::class, Page::class]);
            }
        }

        return $this->customise([
            'Content'     => DBField::create_field('HTMLText', $this->Content),
            'Form'        => $form,
            'PartialLink' => $partial->getPartialLink()
        ])->renderWith([static::class, Page::class]);
    }
}
