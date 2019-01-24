<?php
namespace iLub\Plugin\UnibeCalendarCustomGrid\FileUploadProcessor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use League\Flysystem\Util;
use ILIAS\FileUpload\Processor\PreProcessor;
/**
 * Class FilenameSanitizerPreProcessor
 *
 * PreProcessor which overrides the filename with a given one
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class FilenameOverride implements PreProcessor {

    /**
     * @var string
     */
    protected $filename;
    /**
     * FilenameOverride constructor.
     * @param string $filename
     */
    public function __construct(string $filename){
        $this->filename = $filename;
    }

    /**
	 * @inheritDoc
	 */
	public function process(FileStream $stream, Metadata $metadata) {
		$metadata->setFilename(Util::normalizeRelativePath($this->filename));
		return new ProcessingStatus(ProcessingStatus::OK, 'Filename changed');
	}
}