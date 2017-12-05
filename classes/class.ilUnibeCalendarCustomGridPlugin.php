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
	 * ilCalendarCategory []
	 */
	protected $categories = [];

	/**
	 * Replace the whole appointment presentation in the grid.
	 *
	 * @param string $a_content html grid content
	 *
	 * @return mixed string or empty.
	 */
	public function replaceContent($a_content) {
		/**
		 * @var $DIC \ILIAS\DI\Container
		 */
		global $DIC;

		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		if ($this->isSession() && $this->checkWriteAccess()) {

			$wrapper = $factory->dropzone()->file()->wrapper(
					$this->getUploadURL(),
					$factory->legacy($a_content))->withTitle($this->txt("upload_to")." ".$this->getCategory()->getTitle());

			$wrapper = $wrapper->withAdditionalOnLoadCode(function($id){
				/**
				 * @var $DIC \ILIAS\DI\Container
				 */
				$target = $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				return "il.Unibe.customizeWrapper($id,'$target')";
			});

			return $renderer->render($wrapper); // this seems to be rendered in a very strange place
		}

		return $a_content;
	}

	/**
	 * @return bool
	 */
	public function checkWriteAccess(){
		global $DIC;

		$system = $DIC->rbac()->system();

		$ref_id = array_pop(ilObject::_getAllReferences($this->getCategory()->getObjId()));

		return $system->checkAccess("write",$ref_id);

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
		global $DIC;

		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();

		$event_items = (ilObjectActivation::getItemsByEvent($this->getCategory()->getObjId()));

		$content = "";
		$files = [];

		if(count($event_items))
		{
			include_once('./Services/Link/classes/class.ilLink.php');
			$files = [];
			foreach ($event_items as $item)
			{
				if($item['type'] == "file") {
					$has_files = true;
					$href = ilLink::_getStaticLink($item['ref_id'], "file", true,"download");
					$files[$item['title']] = $renderer->render(
							$factory->button()->shy($item['title'].";", $href));
				}
			}
			if($has_files)
			{
				$content = "</br>Files: ";
				ksort($files, SORT_NATURAL | SORT_FLAG_CASE);
				foreach($files as $file){
					$content .= $file." ";

				}
			}
		}





		return $content;

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
		if ($this->isSession() && $this->checkWriteAccess()) {
			$upload_item = (new Upload($a_item->getTitle()))->withUploadURL($this->getUploadURL());
			$upload_item = $upload_item->copyFromItem($a_item);
			return $upload_item;
		}

		return $a_item;
	}


	/**
	 * @return string
	 */
	public function editShyButtonTitle() {
		return false;
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
			$DIC->ui()
					->mainTemplate()
					->addJavaScript("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/js/customizeWrapper.js");
			$init = true;
		}
	}


	/**
	 * @return \ilCalendarCategory
	 */
	private function getCategory(): \ilCalendarCategory {
		$entry_id = $this->getAppointment()->getEntryId();
		if(! array_key_exists($entry_id, $this->categories)){
			$cat_id = ilCalendarCategoryAssignments::_lookupCategory($entry_id);
			$this->categories[$this->getAppointment()->getEntryId()] = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		}
		return $this->categories[$entry_id];
	}


	/**
	 * @return string
	 */
	private function getUploadURL(): string {
		return (new ilUnibeUploadHandlerGUI())->buildUploadURL($this->getCategory()->getObjId());
	}
}