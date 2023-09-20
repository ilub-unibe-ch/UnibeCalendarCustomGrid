<?php
declare(strict_types=1);
use iLub\Plugin\UnibeCalendarCustomGrid\UI\Item\Upload;
use ILIAS\UI\Component\Item\Item;
use ILIAS\DI\Container;

/**
 * Class ilUnibeCalendarCustomGridPlugin
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ilUnibeCalendarCustomGridPlugin extends ilAppointmentCustomGridPlugin {

    /**
     * ilCalendarCategory []
     */
    protected array $categories = [];
    protected Container $dic;

    public function __construct(ilDBInterface $db, ilComponentRepositoryWrite $component_repository, string $id)
    {
        parent::__construct($db, $component_repository, $id);
        if(empty($this->dic)){
            global $DIC;
            $this->dic = $DIC;
        }
    }

    public final function getPluginName(): string {
		return "UnibeCalendarCustomGrid";
	}


	/**
	 * Replace the whole appointment presentation in the grid.
	 */
    public function replaceContent(string $content): string
    {
		$renderer = $this->dic->ui()->renderer();
		$factory = $this->dic->ui()->factory();
        $this->initJSAndCSS();

		if ($this->isSession() && $this->checkWriteAccess()) {

			$wrapper = $factory->dropzone()->file()->wrapper(
                $this->txt("upload_to")." ".$this->getCategory()->getTitle(),
					$this->getUploadURL(),
					$factory->legacy($content),
                    $factory->input()->field()->file(new ilObjFileUploadHandlerGUI(), ""));
		/*	$wrapper = $wrapper->withAdditionalOnLoadCode(function($id){
				return "il.Unibe.customizeWrapper($id)";
			});*/

			return "<span>".$renderer->render($wrapper)."</span>";
		}

		return $content;
	}

	/**
	 * @return bool
	 */
	public function checkWriteAccess(): bool{
        $ref_array = ilObject::_getAllReferences($this->getCategory()->getObjId());
        $ref_id = array_pop( $ref_array);
		return $this->dic->rbac()->system()->checkAccess("manage_materials",$ref_id,"sess");
	}


	/**
	 * @return bool
	 */
	private function isSession(): bool{
		return $this->getCategory()->getObjType() === "sess";
	}


	/**
	 * @return string or empty.
	 */
	public function addExtraContent(): string {

		$content = "";
		//$content .= $this->getFilesHtml();
		$content .= $this->getMetaDozentenDataHtml();

		return $content;

	}

	protected function getMetaDozentenDataHtml(): string{

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

	protected function getMetaDataValueByTitle(string $title): ilPDOStatement{

		$obj_id = $this->getCategory()->getObjId();
		$query = "SELECT val.value
			FROM adv_md_values_ltext as val
			INNER JOIN adv_mdf_definition as def ON  val.field_id = def.field_id
			WHERE def.title = '$title' AND val.obj_id = $obj_id";
		return $this->dic->database()->query($query);
	}

	protected function getFilesHtml(): string{
		$obj_id = $this->getCategory()->getObjId();


		$file_handler = new ilUnibeFileHandlerGUI(new ilObjFileStakeholder($this->dic->user()->getId()));
		if($file_handler->hasFiles($obj_id)){
            //return  "<span class='il-downloader'><div class=\"glyphicon glyphicon-download-alt\" aria-hidden=\"true\"></div></span>";

            $url = $file_handler->buildDownloadURL($obj_id);
			return  "</button><a class='il-downloader' href='$url'><div class=\"glyphicon glyphicon-download-alt\"></div></a>";

		}

		return "";
	}


	public function addGlyph(): string{
		return "";
	}


	public function editAgendaItem(Item $item): Item {
        $this->initJSAndCSS();

        if($this->isSession() &&  $this->checkWriteAccess()){
			$upload_item = (new Upload($item->getTitle()))->withUploadURL($this->getUploadURL());
			return $upload_item->copyFromItem($item);
		}
		return $item;
	}


	/**
	 * @throws ilDatabaseException
	 */
	public function editShyButtonTitle(): string {
	    $files_glyph = $this->getFilesHtml();
		$short_title = $this->getMetaDataValueByTitle("Kurzbezeichnung")->fetchRow();
		if(!$short_title){
            return $this->getAppointment()->getTitle().$files_glyph;
		}

		$start_time = $this->getAppointment()->getStart()->get(IL_CAL_FKT_DATE,"G:i");
		$end_time = $this->getAppointment()->getEnd()->get(IL_CAL_FKT_DATE,"G:i");
		if($this->dic->ctrl()->getCmdClass()!="ilcalendardaygui"){
            return $start_time."-".$end_time.": ". $short_title['value'].$files_glyph;
        }else{
            return $start_time."-".$end_time.": ". $this->getAppointment()->getTitle().", ".$short_title['value'].$files_glyph;
        }

	}


	private function initJSAndCSS(): void{
		static $init;
		if (!$init) {
			$tpl = $this->dic->ui()->mainTemplate();
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
            $tpl->addJavaScript("./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/js/ServiceOpenLayers.js");
            $init = true;
		}
	}


	private function getCategory(): ilCalendarCategory {
		$entry_id = $this->getAppointment()->getEntryId();
		if(! array_key_exists($entry_id, $this->categories)){
			$cat_id = ilCalendarCategoryAssignments::_lookupCategory($entry_id);
			$this->categories[$this->getAppointment()->getEntryId()] = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		}
		return $this->categories[$entry_id];
	}


	private function getUploadURL(): string {
		return (new ilUnibeFileHandlerGUI(new ilObjFileStakeholder($this->dic->user()->getId())))->buildUploadURL($this->getCategory()->getObjId());
	}
}
