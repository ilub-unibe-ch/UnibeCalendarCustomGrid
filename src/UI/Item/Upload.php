<?php

namespace SRAG\Plugins\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Component\Item\Standard;
use ILIAS\UI\Implementation\Component\Item\Item;

/**
 * Class Upload
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Upload extends Item implements Standard {

	/**
	 * @var string
	 */
	protected $upload_url = '';


	/**
	 * @param $url
	 *
	 * @return \SRAG\Plugins\UnibeCalendarCustomGrid\UI\Item\Upload
	 */
	public function withUploadURL($url) {
		$clone = clone $this;
		$clone->upload_url = $url;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->upload_url;
	}
}
