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
     * ilCalendarCategory []
     */
    protected $categories = [];

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
		/**
		 * @var $DIC \ILIAS\DI\Container
		 */
		global $DIC;

		$renderer = $DIC->ui()->renderer();
		$factory = $DIC->ui()->factory();
        $this->initJSAndCSS();

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

		$ref_id = array_pop(ilObject::_getAllReferences($this->getCategory()->getObjId()));
		return $DIC->rbac()->system()->checkAccess("manage_materials",$ref_id,"sess");
	}


	/**
	 * @return bool
	 */
	private function isSession() {
		return $this->getCategory()->getObjType() === "sess";
	}


	/**
	 * @return string or empty.
	 */
	public function addExtraContent() {

		$content = "";
		//$content .= $this->getFilesHtml();
		$content .= $this->getMetaDozentenDataHtml();

		return $content;

	}

	protected function getMetaDozentenDataHtml(){

		$meta_html = "";

		$res = $this->getMetaDataValueByTitle('Dozierende');

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

	/**
	 * @param string $title
	 * @return ilPDOStatement
	 */
	protected function getMetaDataValueByTitle(string $title){
		global $DIC;

		$obj_id = $this->getCategory()->getObjId();
		$query = "SELECT val.value
			FROM adv_md_values_text as val
			INNER JOIN adv_mdf_definition as def ON  val.field_id = def.field_id
			WHERE def.title = '$title' AND val.obj_id = $obj_id";
		return $DIC->database()->query($query);
	}

	protected function getFilesHtml(){
		$obj_id = $this->getCategory()->getObjId();


		$file_handler = new ilUnibeFileHandlerGUI();
		if($file_handler->hasFiles($obj_id)){
            //return  "<span class='il-downloader'><div class=\"glyphicon glyphicon-download-alt\" aria-hidden=\"true\"></div></span>";

            $url = $file_handler->buildDownloadURL($obj_id);
			return  "</button><a class='il-downloader' href='$url'><div class=\"glyphicon glyphicon-download-alt\"></div></a>";

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
        $this->initJSAndCSS();

        if($this->isSession() &&  $this->checkWriteAccess()){
			$upload_item = (new Upload($a_item->getTitle()))->withUploadURL($this->getUploadURL());
			return $upload_item->copyFromItem($a_item);
		}


		return $a_item;
	}


	/**
	 * @return bool|string
	 * @throws ilDatabaseException
	 */
	public function editShyButtonTitle() {
	    global $DIC;
	    $files_glyph = $this->getFilesHtml();
		$short_title = $this->getMetaDataValueByTitle("Kurzbezeichnung")->fetchRow();
		if(!$short_title){
            return $this->getAppointment()->getTitle().$files_glyph;
		}

		$start_time = $this->getAppointment()->getStart()->get(IL_CAL_FKT_DATE,"G:i");
		$end_time = $this->getAppointment()->getEnd()->get(IL_CAL_FKT_DATE,"G:i");
		if($DIC->ctrl()->getCmdClass()!="ilcalendardaygui"){
            return $start_time."-".$end_time.": ". $short_title['value'].$files_glyph;
        }else{
            return $start_time."-".$end_time.": ". $this->getAppointment()->getTitle().", ".$short_title['value'].$files_glyph;;
        }

	}


	private function initJSAndCSS() {
		static $init;
		if (!$init) {
			global $DIC;

			$tpl = $DIC->ui()->mainTemplate();
            $tpl->addJavaScript("./libs/bower/bower_components/jquery-dragster/jquery.dragster.js");
            $tpl->addJavaScript("./libs/bower/bower_components/fine-uploader/dist/fine-uploader.core.min.js");
            $tpl->addJavaScript("./src/UI/templates/js/Dropzone/File/uploader.js");
            $tpl->addJavaScript("./src/UI/templates/js/Dropzone/File/dropzone.js");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/js/customizeWrapper.js");
            $tpl->addCss("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/css/custom.css");
            $tpl->addJavaScript("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/js/deleteFile.js");
            $tpl->addCss("libs/bower/bower_components/openlayers/build/ol.css");
            $tpl->addJavaScript("libs/bower/bower_components/openlayers/build/ol.js");
            $tpl->addCss("Services/Maps/css/service_openlayers.css");
            $tpl->addJavaScript("Services/Maps/js/ServiceOpenLayers.js");
            $tpl->addJavaScript("Services/Maps/js/ServiceGoogleMaps.js");;
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