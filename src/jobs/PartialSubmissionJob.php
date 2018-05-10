<?php

namespace Firesphere\PartialUserforms\Jobs;

use DNADesign\ElementalUserForms\Model\ElementForm;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;

class PartialSubmissionJob extends AbstractQueuedJob
{

    /**
     * The generated CSV files
     * @var array
     */
    protected $files = [];

    protected $config;

    /**
     * @return string
     */
    public function getTitle()
    {
        return _t(__CLASS__ . '.Title', 'Export partial submissions to Email');
    }

    /**
     * Do some processing yourself!
     */
    public function process()
    {
        $this->config = SiteConfig::current_site_config();
        /** @var DataList|PartialFormSubmission[] $exportForms */
        $allSubmissions = PartialFormSubmission::get()->filter(['IsSend' => false]);
        /** @var ArrayList|UserDefinedForm[]|ElementForm[] $parents */
        $userDefinedForms = ArrayList::create();
        $this->getParents($allSubmissions, $userDefinedForms);


        foreach ($userDefinedForms as $form) {
            $fileName = _t(__CLASS__ . '.Export', 'Export of ') .
                $form->Title . ' - ' .
                DBDatetime::now()->Format(DBDatetime::ISO_DATETIME);
            $file = '/tmp/' . $fileName . '.csv';
            $this->files[] = $file;
            $this->buildCSV($file, $form);
        }

        /** @var Email $mail */
        $mail = Email::create();

        $mail->setSubject('Partial form submissions of ' . DBDatetime::now()->Format(DBDatetime::ISO_DATETIME));
        foreach ($this->files as $file) {
            $mail->addAttachment($file);
        }
        $mail->setTo($this->config->SendMailTo);
        $mail->setFrom('test@example.com');
        $mail->send();

        $this->isComplete = true;
    }

    /**
     * @param $file
     * @param $form
     */
    protected function buildCSV($file, $form)
    {
        $resource = fopen($file, 'w+');
        /** @var PartialFormSubmission $submissions */
        $submissions = PartialFormSubmission::get()->filter(['UserDefinedFormID' => $form->ID]);
        $headerFields = $form
            ->Fields()
            ->exclude(['Name:PartialMatch' => 'EditableFormStep'])
            ->column('Title');
        fputcsv($resource, $headerFields);

        if ($submissions->count()) {
            $this->processSubmissions($form, $submissions, $resource);
        }
        fclose($resource);
    }

    /**
     * @param $form
     * @param $submissions
     * @param $submitted
     * @param $resource
     */
    protected function processSubmissions($form, $submissions, $resource)
    {
        $editableFields = $form->Fields()->map('Name', 'Title')->toArray();
        $submitted = [];
        $fieldMap = [];
        foreach ($submissions as $submission) {
            $values = $submission->PartialFields()->map('Name', 'Value')->toArray();
            $i = 0;
            foreach ($editableFields as $field => $title) {
                $submitted[] = '';
                $fieldMap[$i]['Name'] = $field;
                $fieldMap[$i]['Title'] = $title;
                $fieldMap[$i]['ParentClass'] = UserDefinedForm::class;
                $fieldMap[$i]['Parent'] = '=>' . UserDefinedForm::class . '.form1';
                if (isset($values[$field])) {
                    $submitted[] = $values[$field];
                }
                $i++;
            }
            fputcsv($resource, $submitted);
            $submission->IsSend = true;
            $submission->write();
        }
    }
    
    public function afterComplete()
    {
        parent::afterComplete();
        if ($this->config->CleanupAfterSend) {
            /** @var DataList|PartialFormSubmission[] $forms */
            $forms = PartialFormSubmission::get()->filter(['IsSend' => true]);
            foreach ($forms as $form) {
                /** @var DataList|PartialFieldSubmission[] $fields */
                $fields = PartialFieldSubmission::get()->filter(['ID' => $form->PartialFields()->column('ID')]);
                $fields->removeAll();
                $form->delete();
                $form->destroy();
            }
        }
    }/**
 * @param $allSubmissions
 * @param $userDefinedForms
 */protected function getParents($allSubmissions, &$userDefinedForms)
{
    /** @var PartialFormSubmission $submission */
    foreach ($allSubmissions as $submission) {
        // Due to having to support Elemental ElementForm, we need to manually get the parent
        // It's a bit a pickle, but it works
        $parentClass = $submission->ParentClass;
        $parent = $parentClass::get()->byID($submission->UserDefinedFormID);
        if ($parent &&
            $parent->ExportPartialSubmissions &&
            !$userDefinedForms->find('ID', $parent->ID)
        ) {
            $userDefinedForms->push($parent);
        }
        $submission->destroy();
    }
}
}
