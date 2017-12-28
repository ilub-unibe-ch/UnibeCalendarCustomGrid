<?php

namespace SRAG\Plugins\UnibeCalendarCustomGrid\Ctrl;

/**
 * Class ICtrlAware
 *
 * Provides base functionality which is needed when implementing controller classes in ILIAS using
 * ilCtrl
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ICtrlAware {

	const CMD_UPLOAD = "upload";
	const CMD_DOWNLOAD = "download";
	const CMD_DELETE = "delete";

	public function executeCommand();


	/**
	 * @return ICtrlAware
	 */
	public function getParentController();


	/**
	 * @param ICtrlAware $ctrlAware
	 */
	public function setParentController(ICtrlAware $ctrlAware);


	public function upload();


	public function download();


	/**
	 * @return array of ClassNames this Controller can call using ILIAS ilCtrl
	 */
	public function getPossibleNextClasses();


	/**
	 * @return \ilCtrl
	 */
	public function ctrl();


	/**
	 * @return \ilTemplate the global Instance
	 */
	public function tpl();


	/**
	 * @return \ilLanguage
	 */
	public function language();


	/**
	 * @return \ilTabsGUI
	 */
	public function tabs();


	/**
	 * @return \ilObjUser
	 */
	public function user();


	/**
	 * @return \ilAccessHandler
	 */
	public function access();


	/**
	 * @return \ILIAS\DI\HTTPServices
	 */
	public function http();


	/**
	 * @return \ilTree
	 */
	public function tree();


	/**
	 * @param \SRAG\Learnplaces\gui\helper\ICtrlAware $ctrlAware the current controller
	 *
	 * @return bool whether a next class has handled the request or not
	 */
	public function handleNextClass(ICtrlAware $ctrlAware);
}
