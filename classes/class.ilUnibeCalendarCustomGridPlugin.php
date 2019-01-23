<?php

use iLub\Plugin\UnibeCalendarCustomGrid\UI\Item\Upload;

require_once('./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/vendor/autoload.php');

/**
 * Class ilUnibeCalendarCustomGridPlugin
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
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
				return "il.Unibe.customizeWrapper($id)";
			});

			$wrapper= $wrapper->withUserDefinedFileNamesEnabled(true);

			return "<span>".$renderer->render($wrapper)."</span>";
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

		return $system->checkAccess("manage_materials",$ref_id);

	}


	/**
	 * @return bool
	 */
	private function isSession() {
		$cat = $this->getCategory();

		$is_session = $cat->getObjType() === "sess";

		if ($is_session) {
			$this->initJSAndCSS();
		}

		return $is_session;
	}


	/**
	 * @return string or empty.
	 */
	public function addExtraContent() {

		$content = "";
		$content .= $this->getFilesHtml();
		$content .= $this->getMetaDataHtml();

		return $content;

	}

	protected function getMetaDataHtml(){
		global $DIC;

		$meta_html = "";

		$obj_id = $this->getCategory()->getObjId();
		$query = "SELECT val.value
			FROM adv_md_values_text as val
			INNER JOIN adv_mdf_definition as def ON  val.field_id = def.field_id
			WHERE def.title = 'Dozierende' AND val.obj_id = $obj_id";
		$res = $DIC->database()->query($query);

		if($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$meta_html .= "<div class='il-dozenten'> (";
			$exploded = explode(",",$row->value);
			foreach($exploded as $complete_link){
				$name_only = trim(strip_tags ($complete_link));
				$words = explode(" ", $name_only);
				$acronym = "";
				foreach ($words as $w) {
					$acronym .= $w[0];
				}
				$short_link = str_replace($name_only,$acronym,$complete_link);
				$meta_html .= $short_link.", ";
			}
			$meta_html = rtrim($meta_html,", ").")</div>";

		}

		return $meta_html;
	}

	protected function getFilesHtml(){
		$obj_id = $this->getCategory()->getObjId();


		$file_handler = new ilUnibeFileHandlerGUI();
		if($file_handler->hasFiles($obj_id)){
			$url = $file_handler->buildDownloadURL($obj_id);
			return  "<a class='il-downloader' href='$url'><div class=\"glyphicon glyphicon-download-alt\" aria-hidden=\"true\"></div></a>";

		}

		return "";
	}


	/**
	 * @return string
	 */
	public function addGlyph() {
		return false;
	}


	/**
	 * @param \ILIAS\UI\Component\Item\Item $a_item
	 *
	 * @return \ILIAS\UI\Component\Item\Item
	 */
	public function editAgendaItem(\ILIAS\UI\Component\Item\Item $a_item) {
		if($this->isSession() ){
			$upload_item = (new Upload($a_item->getTitle()))->withUploadURL($this->getUploadURL());

			if ($this->checkWriteAccess()) {
				$upload_item = $upload_item->copyFromItem($a_item);
			}
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


	private function initJSAndCSS() {
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
			$DIC->ui()
					->mainTemplate()
					->addCss("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/css/custom.css");
			$DIC->ui()
					->mainTemplate()
					->addJavaScript("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/js/deleteFile.js");
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
		return (new ilUnibeFileHandlerGUI())->buildUploadURL($this->getCategory()->getObjId());
	}
}