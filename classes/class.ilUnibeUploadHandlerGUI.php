<?php

use SRAG\Plugins\UnibeCalendarCustomGrid\Ctrl\CtrlAware;
use SRAG\Plugins\UnibeCalendarCustomGrid\Ctrl\ICtrlAware;

require_once('./Customizing/global/plugins/Services/Calendar/AppointmentCustomGrid/UnibeCalendarCustomGrid/vendor/autoload.php');

/**
 * Class ilUnibeUploadHandlerGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilUnibeUploadHandlerGUI: ilUIPluginRouterGUI
 */
class ilUnibeUploadHandlerGUI implements ICtrlAware {

	const P_SESSION_OBJ_ID = 'session_id';
	use CtrlAware;
	/**
	 * @var int
	 */
	protected $obj_id = 0;
	/**
	 * @var int
	 */
	protected $ref_id = 0;


	/**
	 * @param int $session_obj_id
	 *
	 * @return string
	 */
	public function buildUploadURL($session_obj_id) {
		$this->ctrl()->setParameter($this, self::P_SESSION_OBJ_ID, $session_obj_id);

		return $this->ctrl()->getLinkTargetByClass([
			ilUIPluginRouterGUI::class,
			self::class,
		], self::CMD_INDEX, '', true);
	}


	public function index() {
		global $DIC;
		$this->initIDsFromRequest();

		$upload = $DIC->upload();
		try {
			$upload->process();

			$message = "";
			foreach ($upload->getResults() as $tempname => $result) {
				$message .= $this->handleFileUpload($tempname, $result);
			}

			// The File-Dropzones will expect a valid json-Status (success true or false).
			echo json_encode([ 'success' => true, 'message' => $message ]);
		} catch (Exception $e) {
			echo json_encode([ 'success' => false, 'message' => $e->getMessage() ]);
		}
		exit();
	}


	/**
	 * @param string                             $tempname
	 * @param \ILIAS\FileUpload\DTO\UploadResult $result
	 */
	private function handleFileUpload(string $tempname, \ILIAS\FileUpload\DTO\UploadResult $result) {
		global $DIC;

		$file = new \ilObjFile();
		$file->setTitle($result->getName());
		$file->setDescription('');
		$file->setFileName($result->getName());
		$file->setFileType($result->getMimeType());
		$file->setFileSize($result->getSize());
		$file->create();
		$new_ref_id = $file->createReference();
		$file->putInTree($this->tree()->getParentId($this->getRefId()));
		$file->setPermissions($this->tree()->getParentId($this->getRefId()));
		$file->createDirectory();
		$file->getUploadFile($tempname, $result->getName());

		/*
		 * This would be the "right" way to do it, however this can create race conditions
		 * in multiple file upload. Therefore we execute the query directly here.
		$ev = new ilEventItems($this->getObjId());
		$ev->addItem($new_ref_id);
		$ev->update();
		 */
		$query = "INSERT INTO event_items (event_id,item_id) ".
				"VALUES( ".
				$DIC->database()->quote($this->getObjId() ,'integer').", ".
				$DIC->database()->quote($new_ref_id ,'integer')." ".
				")";
		$DIC->database()->manipulate($query);

		return "Inserted file with ref_id: ".$new_ref_id." into event_id: .".$this->getObjId();
	}


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->obj_id;
	}


	/**
	 * @param int $obj_id
	 */
	public function setObjId(int $obj_id) {
		$this->obj_id = $obj_id;
	}


	/**
	 * @return int
	 */
	public function getRefId(): int {
		return $this->ref_id;
	}


	/**
	 * @param int $ref_id
	 */
	public function setRefId(int $ref_id) {
		$this->ref_id = $ref_id;
	}


	private function initIDsFromRequest() {
		$this->setObjId($this->http()->request()->getQueryParams()[self::P_SESSION_OBJ_ID]);
		$ref_ids = array();
		foreach (ilObject::_getAllReferences($this->getObjId()) as $ref_id) {
			if ($this->access()->checkAccess("read", "", $ref_id)) {
				$ref_ids[] = $ref_id;
			}
		}

		$this->setRefId((int)current($ref_ids));
	}
}
