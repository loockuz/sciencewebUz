<?php

/**
 * @file SciencewebUzPlugin.inc.php
 *
 * Copyright (c) 2022 I-EDU GROUP by Dilshod Tursimatov
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SciencewebUzPlugin extends GenericPlugin
{
	/**
	 * Get the display name of this plugin
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return "SciencewebUz";
	}

	/**
	 * Get the description of this plugin
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return "DESCRIPTION";
	}

	/**
	 * @copydoc Plugin::register()
	 *
	 * @param null|mixed $mainContextId
	 */
	public function register($category, $path, $mainContextId = null)
	{
		if (!parent::register($category, $path, $mainContextId)) {
			return false;
		}
		if ($this->getEnabled($mainContextId)) {
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEdit'));

			// Hook for initData in two forms -- init the new fields
			HookRegistry::register('submissionsubmitstep3form::initdata', array($this, 'metadataInitData'));
			HookRegistry::register('issueentrysubmissionreviewform::initdata', array($this, 'metadataInitData'));

			// Hook for readUserVars in two forms -- consider the new field entries
			HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'metadataReadUserVars'));
			HookRegistry::register('issueentrysubmissionreviewform::readuservars', array($this, 'metadataReadUserVars'));


			// Hook for execute in two forms -- consider the new fields in the article settings
			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'metadataExecute'));
			HookRegistry::register('issueentrysubmissionreviewform::execute', array($this, 'metadataExecute'));

			// Hook for save in two forms -- add validation for the new fields
			HookRegistry::register('submissionsubmitstep3form::Constructor', array($this, 'addCheck'));
			HookRegistry::register('issueentrysubmissionreviewform::Constructor', array($this, 'addCheck'));
		}
		return true;
	}

	/**
	 * Init article custom metadata fields
	 */
	function metadataInitData($hookName, $params)
	{		
		$form =& $params[0];
		if (get_class($form) == 'SubmissionSubmitStep3Form') {
			$article = $form->submission;
		} elseif (get_class($form) == 'IssueEntrySubmissionReviewForm') {
			$article = $form->getSubmission();
		} elseif (get_class($form) == 'QuickSubmitForm') {
			$article = $form->submission;
		}
		$customCategories = $form->getData('customCategories');

        $form->setData('customCategories', $customCategories);
		return false;
	}

	/**
	 * Add check/validation
	 */
	function addCheck($hookName, $params) {
		$form =& $params[0];
		
		# Requires some changes to the plugin database
		
		return false;
	}
 
	/**
	 * Set article custom metadata fields
	 */
	function metadataExecute($hookName, $params)
	{
		$form =& $params[0];

        self::logFileWrite($form, "metadataExecute");
        switch (get_class($form)) {
			case 'SubmissionSubmitStep3Form':

            case 'QuickSubmitForm':
				$article = $form->submission;
				break;
			case 'IssueEntrySubmissionReviewForm':
                $article = $form->getSubmission();
				break;
			default: throw new Exception('Unknown class form ' . get_class($form));
		}
		$valueCategories = $form->getData("customCategories");

        $form->setData("customCategories", "dilshod");
        $article->setData("customCategories", "dilshod");

		return false;
	}
    public function logFileWrite($varable, $nameLog){
        ob_start();
        var_dump($varable);
        $result = ob_get_clean();
        $file = fopen($nameLog."log.txt", "w");
        fwrite($file, $result);
        fclose($file);
    }
	/**
	 * Concern custom metadata fields in the form
	 */
	function metadataReadUserVars($hookName, $params)
	{
		$userVars =& $params[1];
		$userVars[] = "customCategories";

        return false;
	}

	/*
	 * Metadata
	 */

	/**
	 * Insert custom metadata fields into author submission step 3 and metadata edit form
	 */
	function metadataFieldEdit($hookName, $params)
	{
		$smarty =& $params[1];
		$output =& $params[2];



		$fbv = $smarty->getFBV();
		$form = $fbv->getForm();

		if (get_class($form) == 'SubmissionSubmitStep3Form') {
			$submission = $form->submission;
		} elseif (get_class($form) == 'IssueEntrySubmissionReviewForm') {
			$submission = $form->getSubmission();
		}
        $select = json_encode($form);
        $file = fopen("metadataFieldEdit.txt", "w");
        fwrite($file, $select);
        fclose($file);
		$smarty->assign('customCategories', $submission->getData("customCategories"));
		$dataValue = $submission->getData("customCategories");
		$output .= $smarty->fetch($this->getTemplateResource('textinput.tpl'));
		return false;
	}
	
	/**
	 * @copydoc Plugin::getActions()
	 */
	public function getActions($request, $verb)
	{
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled() ? array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			) : array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	public function manage($args, $request)
	{
		switch ($request->getUserVar('verb')) {
			case 'settings':
				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
				$this->import('SciencewebUzSettingsForm');
				$form = new SciencewebUzSettingsForm($this, $request->getContext()->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$notificationManager = new NotificationManager();
						$notificationManager->createTrivialNotification($request->getUser()->getId());
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}
}
