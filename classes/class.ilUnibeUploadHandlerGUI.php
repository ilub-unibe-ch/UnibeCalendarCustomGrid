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

			foreach ($upload->getResults() as $tempname => $result) {
				$this->handleFileUpload($tempname, $result);
			}

			// The File-Dropzones will expect a valid json-Status (success true or false).
			echo json_encode([ 'success' => true, 'message' => 'Successfully uploaded file' ]);
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
		$ev = new ilEventItems($this->getObjId());
		$items = $ev->getItems();

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

		$items[] = $new_ref_id;

		$ev->setItems($items);
		$ev->update();
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
