<?php
include_once("./Services/Calendar/classes/class.ilAppointmentCustomGridPlugin.php");

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

			$wrapper = $factory->dropzone()->file()->wrapper("#", $factory->legacy($a_content));

			return $renderer->render($wrapper); // this seems to be rendered in a very strahnge place
		}

		return $a_content;
	}


	/**
	 * @return bool
	 */
	private function isSession() {
		$appointment = $this->getAppointment();

		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($appointment->getEntryId());
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);

		return $cat->getObjType() === "sess";
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
		// global $DIC;
		// return $DIC->ui()->factory()->dropzone()->file()->wrapper("#", $a_item);
		return $a_item; // here we should be able to wrap the item with a dropzone, surrently not possible
	}


	/**
	 * @return string
	 */
	public function editShyButtonTitle() {
		return "[PLUGIN] editShyButtonTitle"; // Where is this rendered?
	}
}