<?php

declare(strict_types=1);

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use iLub\Plugin\UnibeCalendarCustomGrid\FileUploadProcessor\FilenameOverride;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Class ilUnibeFileHandlerGUI
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * @ilCtrl_isCalledBy ilUnibeFileHandlerGUI: ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy ilUnibeFileHandlerGUI: ilDashboardGUI
 * @ilCtrl_isCalledBy ilUnibeFileHandlerGUI: ilUnibeCalendarCustomModalPlugin
 * @ilCtrl_IsCalledBy ilUnibeFileHandlerGUI: ilCalendarPresentationGUI
 */
class ilUnibeFileHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    public const P_SESSION_OBJ_ID = 'session_id';
    public const P_FILE_REF_ID = 'file_id';
    protected const CMD_DOWNLOAD = 'download';
    protected const CMD_DELETE = 'delete';

    protected int $obj_id = 0;
    protected int $ref_id = 0;
    protected int $file_id = 0;

    protected Container $dic;


    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->dic = $DIC;
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getCmd()) {
            case self::CMD_DOWNLOAD:
                $this->download();
                break;
            case self::CMD_DELETE:
                $this->delete();
                break;
            default:
                parent::executeCommand();
                break;
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function getUploadURL(): string
    {
        $this->ctrl->setParameter($this, self::P_SESSION_OBJ_ID, $this->getObjId());

        return $this->ctrl->getLinkTargetByClass([
            ilUIPluginRouterGUI::class,
            self::class,
        ], self::CMD_UPLOAD, null, true);
    }

    /**
     * @throws ilCtrlException
     */
    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_INFO,
            null,
            true
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_REMOVE,
            null,
            true
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function getDownloadURL($obj_id): string
    {
        $this->ctrl->setParameter($this, self::P_SESSION_OBJ_ID, $obj_id);
        return $this->ctrl->getLinkTargetByClass([
                ilUIPluginRouterGUI::class,
                self::class,
        ], self::CMD_DOWNLOAD, '', true);
    }

    /**
     * @throws ilCtrlException
     */
    public function getDeleteAction(int $obj_id, int $file_id): string
    {
        $this->ctrl->setParameter($this, self::P_SESSION_OBJ_ID, $obj_id);
        $this->ctrl->setParameter($this, self::P_FILE_REF_ID, $file_id);

        $async_url = $this->ctrl->getLinkTargetByClass([
                ilUIPluginRouterGUI::class,
                self::class,
        ], 'delete', '', true);
        return "il.Unibe.deleteFile(this,'$async_url');";
    }

    public function delete(): void
    {
        global $DIC;

        $this->initIDsFromRequest();


        $file = new ilObjFile($this->getFileId());
        $query = 'DELETE FROM event_items ' .
            'WHERE event_id = ' .$DIC->database()->quote($this->getObjId(), 'integer').
            ' AND item_id = ' .$DIC->database()->quote($this->getFileId(), 'integer'). ' ';
        $DIC->database()->manipulate($query);
        $file->delete();
        $session = new ilObjSession($this->ref_id);
        echo json_encode(['message' => $file->getTitle(). ' Deleted',
                          'file_title' => $file->getTitle(),
                          'session_title' => $session->getTitle()]);
        exit;
    }

    /**
     * @param $obj_id
     * @return bool
     */
    public function hasFiles($obj_id): bool
    {
        $event_items = ilObjectActivation::getItemsByEvent($obj_id);

        if (count($event_items)) {
            foreach ($event_items as $item) {
                if ($item['type'] == 'file') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function download(): void
    {
        global $DIC;
        $this->initIDsFromRequest();

        $session = new ilObjSession($this->ref_id);
        $event_items = ilObjectActivation::getItemsByEvent($this->obj_id);

        $files_count = 0;
        $file_path = '';
        $file = null;



        if (count($event_items)) {
            $temp = $DIC->filesystem()->storage();
            $store = $DIC->filesystem()->storage();
            foreach($temp->listContents('calendarout/')as $file) {
                    $temp->deleteDir($file->getPath());
                    }
            $temp_folder_name = 'calendarout/' .uniqid();
            foreach ($event_items as $item) {
                if ($item['type'] == 'file') {
                    $files_count++;
                    $file = new ilObjFile((int)$item['ref_id']);
                    $file_name =  $file->getFileName();
                    $file_path = $file->getDirectory($file->getVersion()). '/data';
                    $rel_file_path = str_replace(CLIENT_DATA_DIR, '', $file_path);
                    $stream = $store->readStream($rel_file_path);
                    $full_temp_path = "$temp_folder_name/$file_name";
                    if(!$temp->has($full_temp_path)) {
                        $temp->writeStream($full_temp_path, $stream);
                    }
                }
            }

            if($files_count == 1) {
                $temp->deleteDir($temp_folder_name);

                ilFileDelivery::deliverFileAttached($file_path, $file->getFileName(), $file->getFileType());
            } else {
                $download_name = str_replace('/', '',$session->getTitle(). '.zip');
                $tmp_zip_folder = CLIENT_DATA_DIR. '/' .$temp_folder_name;

                $streams = [];
                foreach(glob($tmp_zip_folder. '/*')as $file){
                    $streams[] = Streams::ofResource(fopen($file, 'rb'));
                }
                $zip_options = $DIC->archives()->zipOptions()->withZipOutputPath($tmp_zip_folder)->withZipOutputName($download_name);
                $zip = $DIC->archives()->zip($streams, $zip_options);

                $DIC->fileDelivery()->delivery()->attached(
                    $zip->get(),
                    $download_name,
                    'application/zip'
                );
            }
        }
    }

    /**
     * See Issue: http://ilublx3.unibe.ch:8080/mantis/view.php?id=1368
     * @param string $filename
     * @return mixed|null|string|string[]
     */
    public function customConvertToASCII(string $filename): string
    {
        $umlautsI = [
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',
            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss'
        ];
        foreach($umlautsI as $src => $tgt) {
            $filename = str_replace($src, $tgt, $filename);
        }

        $filename = mb_convert_encoding($filename, 'ASCII');

        $umlautsII = [
            'A?' => 'Ae', 'O?' => 'Oe', 'U?' => 'Ue',
            'a?' => 'ae', 'o?' => 'oe', 'u?' => 'ue', 's?' => 'ss'
        ];
        foreach($umlautsII as $src => $tgt) {
            $filename = str_replace($src, $tgt, $filename);
        }

        return $filename;

    }

    /**
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException
     */
    private function handleFileUpload(UploadResult $result): string
    {
        global $DIC;

        $file = new ilObjFile();

        $file->setTitle($result->getName());
        $file->setFileName($result->getName());
        $file->setDescription('');
        $file->create();
        $new_ref_id = $file->createReference();
        $file->putInTree($this->dic->repositoryTree()->getParentId($this->getRefId()));
        $file->setPermissions($this->dic->repositoryTree()->getParentId($this->getRefId()));
        $file->appendUpload($result, $result->getName());

        /**
         * This would be the "right" way to do it, however this can create race conditions
         * in multiple file upload. Therefore, we execute the query directly here.
        $ev = new ilEventItems($this->getObjId());
        $ev->addItem($new_ref_id);
        $ev->update();
         */
        $query = 'INSERT INTO event_items (event_id,item_id) ' .
            'VALUES( ' .
                $DIC->database()->quote($this->getObjId(), 'integer'). ', ' .
                $DIC->database()->quote($new_ref_id, 'integer'). ' ' .
            ')';
        $DIC->database()->manipulate($query);

        return 'Inserted file with ref_id: ' .$new_ref_id. ' into event_id: .' .$this->getObjId();
    }


    /**
     * @return int
     */
    public function getObjId(): int
    {
        return $this->obj_id;
    }


    /**
     * @param int $obj_id
     */
    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }


    /**
     * @return int
     */
    public function getRefId(): int
    {
        return $this->ref_id;
    }


    /**
     * @param int $ref_id
     */
    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }


    private function initIDsFromRequest(): void
    {
        $query_wrapper = $this->dic->http()->wrapper()->query();
        $this->setObjId(
            $query_wrapper->retrieve(
                self::P_SESSION_OBJ_ID,
                $this->dic->refinery()->kindlyTo()->int()
            )
        );
        if($query_wrapper->has(self::P_FILE_REF_ID)) {
            $this->setFileId($query_wrapper->retrieve(self::P_FILE_REF_ID, $this->dic->refinery()->kindlyTo()->int()));
        }
        $ref_ids = [];

        foreach (ilObject::_getAllReferences($this->getObjId()) as $ref_id) {
            if ($this->dic->access()->checkAccess('read', '', $ref_id)) {
                $ref_ids[] = $ref_id;
            }
        }

        $this->setRefId((int)current($ref_ids));
    }

    /**
     * @return int
     */
    public function getFileId(): int
    {
        return $this->file_id;
    }

    /**
     * @param int $file_id
     */
    public function setFileId(int $file_id): void
    {
        $this->file_id = $file_id;
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        if (null !== ($id = $this->dic->storage->manage()->find($identifier))) {
            $revision = $this->dic->storage->manage()->getCurrentRevision($id)->getInformation();
            $title = $revision->getTitle();
            $size = $revision->getSize();
            $mime = $revision->getMimeType();
        } else {
            $title = $mime = 'unknown';
            $size = 0;
        }

        return new BasicFileInfoResult($this->getFileIdentifierParameterName(), $identifier, $title, $size, $mime);
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    protected function getUploadResult(): HandlerResult
    {

        global $DIC;
        $this->initIDsFromRequest();

        $upload = $DIC->upload();

        if($_POST['customFileName']) {
            $upload->register(new FilenameOverride($this->customConvertToASCII($_POST['customFileName'])));
        }

        try {
            $upload->process();

            $message = '';
            foreach ($upload->getResults() as $tempname => $result) {
                $message .= $this->handleFileUpload($result);
            }
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                md5(random_bytes(65)),
                'file upload OK'
            );
        } catch (Exception $e) {
            return new BasicHandlerResult(
                '',
                HandlerResult::STATUS_FAILED,
                '',
                $e->getMessage()
            );
        }
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        if (null !== $this->dic->storage->manage()->find($identifier)) {
            $message = 'file removal OK';
        } else {
            $message = "file with identifier '$identifier' doesn't exist, nothing to do.";
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }
}
