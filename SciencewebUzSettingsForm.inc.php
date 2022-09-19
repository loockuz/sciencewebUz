<?php

/**
 * @file SciencewebUzSettingsForm.inc.php
 *
 * Copyright (c) 2022 I-EDU GROUP by Dilshod Tursimatov
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 */

import('lib.pkp.classes.form.Form');

class SciencewebUzSettingsForm extends Form
{
    /** @var int Associated context ID */
	private $_contextId;

	private $_plugin;

	/**
	 * Constructor
	 * @param $contextId int Context ID
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$contextId = $this->_contextId;
		$plugin = $this->_plugin;

		$this->setData('sciencewebUzToken', $plugin->getSetting($contextId, 'sciencewebUzToken'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('sciencewebUzToken'));

		

	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request);
	}
 
	/**
	 * @copydoc Form::execute()
	 */
	function execute(...$functionArgs) {
		$plugin = $this->_plugin;
		$contextId = $this->_contextId;

		$plugin->updateSetting($contextId, 'sciencewebUzToken', $this->getData('sciencewebUzToken'));

		parent::execute(...$functionArgs);
	}
}