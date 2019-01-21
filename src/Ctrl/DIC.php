<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\Ctrl;

/**
 * Class DIC
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait DIC  {

	/**
	 * @var \ilAccessHandler
	 */
	private $access;
	/**
	 * @var \ilObjUser
	 */
	private $user;
	/**
	 * @var \ilCtrl
	 */
	private $ctrl;
	/**
	 * @var \ilTemplate
	 */
	private $tpl;
	/**
	 * @var \ilLanguage
	 */
	private $language;
	/**
	 * @var \ilTabsGUI
	 */
	private $tabs;


	/**
	 * @return \ILIAS\DI\Container
	 */
	private function dic() {
		return $GLOBALS['DIC'];
	}


	/**
	 * @return \ilCtrl
	 */
	public function ctrl() {
		return $this->dic()->ctrl();
	}


	/**
	 * @param $variable
	 *
	 * @return string
	 */
	public function txt($variable) {
		return $this->dic()->language()->txt($variable);
	}


	/**
	 * @return \ilTemplate
	 */
	public function tpl() {
		return $this->dic()->ui()->mainTemplate();
	}


	/**
	 * @return \ilLanguage
	 */
	public function language() {
		return $this->dic()->language();
	}


	/**
	 * @return \ilTabsGUI
	 */
	public function tabs() {
		return $this->dic()->tabs();
	}


	/**
	 * @return \ILIAS\DI\UIServices
	 */
	public function ui() {
		return $this->dic()->ui();
	}


	/**
	 * @return \ilObjUser
	 */
	public function user() {
		return $this->dic()->user();
	}


	/**
	 * @return \ilAccessHandler
	 */
	public function access() {
		return $this->dic()->access();
	}


	/**
	 * @return \ILIAS\DI\HTTPServices
	 */
	public function http() {
		return $this->dic()->http();
	}


	/**
	 * @return \ilTree
	 */
	public function tree() {
		return $this->dic()->repositoryTree();
	}


	/**
	 * @return int
	 */
	protected function getCurrentRefId() {
		try {
			$http = $this->dic()->http();
			$ref_id = (int)$http->request()->getQueryParams()["ref_id"];
		} catch (\Exception $e) {
			$ref_id = (int)$_GET["ref_id"];
		}

		return $ref_id;
	}
}
