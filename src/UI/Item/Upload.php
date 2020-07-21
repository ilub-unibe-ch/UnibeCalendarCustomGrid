<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Implementation\Component\Item\Standard;
use ILIAS\UI\Component\Item\Item;
use ILIAS\UI\Component\Image\Image;
use iLub\Plugin\UnibeCalendarCustomGrid\UI\Item\Upload as DefaultUpload;


/**
 * Class Upload
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class Upload extends  Standard implements Item {

	/**
	 * @var string
	 */
	protected $upload_url = '';

	/**
	 * @param Item $item
	 * @return \ILIAS\UI\Component\Item\Item|Item|Upload
	 */
	public function copyFromItem(Item $item){
		$clone = clone $this;

		if (is_array($item->getProperties())) {
			$clone = $clone->withProperties($item->getProperties());
		}
		if ($item->getDescription()) {
			$clone = $clone->withDescription($item->getDescription());
		}
		if ($item->getActions()) {
			$clone = $clone->withActions($item->getActions());
		}

		return $clone;
	}

	public function copyToItem(Item $item){
		if (is_array($this->getProperties())) {
			$item = $item->withProperties($this->getProperties());
		}
		if ($this->getDescription()) {
			$item = $item->withDescription($this->getDescription());
		}
		if ($this->getActions()) {
			$item = $item->withActions($this->getActions());
		}

		return $item;
	}


	/**
	 * @param $url
	 *
	 * @return DefaultUpload
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
