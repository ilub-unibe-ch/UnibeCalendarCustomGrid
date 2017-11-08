<?php

use SRAG\Plugins\UnibeCalendarCustomGrid\UI\Item\Upload;

require_once('./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/vendor/autoload.php');

/**
 * Class ilUnibeCalendarCustomGridPlugin
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUnibeCalendarCustomGridPlugin extends ilAppointmentCustomGridPlugin {

	/**
	 * @return    string    Plugin Name
	 */
	public final function getPluginName() {
		return "UnibeCalendarCustomGrid";
	}


	/**
	 * Replace the whole appointment presentation in the grid.
	 *
	 * @param string $a_content html grid content
	 *
	 * @return mixed string or empty.
	 */
	public function replaceContent($a_content) {
		global $DIC;

		if ($this->isSession()) {
			$renderer = $DIC->ui()->renderer();
			$factory = $DIC->ui()->factory();

			$wrapper = $factory->dropzone()->file()->wrapper($this->getUploadURL(), $factory->legacy($a_content));

			return $renderer->render($wrapper); // this seems to be rendered in a very strahnge place
		}

		return $a_content;
	}


	/**
	 * @return bool
	 */
	private function isSession() {
		$cat = $this->getCategory();

		$is_session = $cat->getObjType() === "sess";

		if ($is_session) {
			$this->initJS();
		}

		return $is_session;
	}


	/**
	 * @return string or empty.
	 */
	public function addExtraContent() {
		return "";
	}


	/**
	 * @return string
	 */
	public function addGlyph() {
		return "";
	}


	/**
	 * @param \ILIAS\UI\Component\Item\Item $a_item
	 *
	 * @return \ILIAS\UI\Component\Item\Item
	 */
	public function editAgendaItem(\ILIAS\UI\Component\Item\Item $a_item) {
		if ($this->isSession()) {

			$item = (new Upload($a_item->getTitle()))->withUploadURL($this->getUploadURL());

			return $item;
		}

		return $a_item;
	}


	/**
	 * @return string
	 */
	public function editShyButtonTitle() {
		return "[PLUGIN] editShyButtonTitle"; // Where is this rendered?
	}


	private function initJS() {
		static $init;
		if (!$init) {
			global $DIC;
			$DIC->ui()
			    ->mainTemplate()
			    ->addJavaScript("./libs/bower/bower_components/jquery-dragster/jquery.dragster.js");
			$DIC->ui()
			    ->mainTemplate()
			    ->addJavaScript("./libs/bower/bower_components/fine-uploader/dist/fine-uploader.core.min.js");
			$DIC->ui()
			    ->mainTemplate()
			    ->addJavaScript("./src/UI/templates/js/Dropzone/File/uploader.js");
			$DIC->ui()
			    ->mainTemplate()
			    ->addJavaScript("./src/UI/templates/js/Dropzone/File/dropzone.js");
			$init = true;
		}
	}


	/**
	 * @return \ilCalendarCategory
	 */
	private function getCategory(): \ilCalendarCategory {
		$appointment = $this->getAppointment();

		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($appointment->getEntryId());
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);

		return $cat;
	}


	/**
	 * @return string
	 */
	private function getUploadURL(): string {
		return (new ilUnibeUploadHandlerGUI())->buildUploadURL($this->getCategory()->getObjId());
	}
}