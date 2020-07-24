<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Implementation\Component\Item\Standard;
use ILIAS\UI\Component\Item\Standard as StandardItem;


/**
 * Class Upload
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class Upload extends  Standard implements StandardItem {

	/**
	 * @var string
	 */
	protected $upload_url = '';

	/**
	 * @param StandardItem $item
	 * @return Upload
	 */
	public function copyFromItem(StandardItem $item){
		$clone = clone $this;

		if (is_array($item->getProperties())) {
			$clone = $clone->withProperties($item->getProperties());
		}
		if ($item->getDescription()) {
			$clone = $clone->withDescription($item->getDescription());
		}
        if ($item->getColor()) {
            $clone = $clone->withColor($item->getColor());
        }
        if ($item->getLead()) {
            $clone = $clone->withLeadText($item->getLead());
        }
		if ($item->getActions()) {
			$clone = $clone->withActions($item->getActions());
		}

		return $clone;
	}

	public function copyToItem(StandardItem $item){
		if (is_array($this->getProperties())) {
			$item = $item->withProperties($this->getProperties());
		}
		if ($this->getDescription()) {
			$item = $item->withDescription($this->getDescription());
		}
		if ($this->getActions()) {
			$item = $item->withActions($this->getActions());
		}
        if ($this->getColor()) {
            $item = $item->withColor($this->getColor());
        }
        if ($this->getLead()) {
            $item = $item->withLeadText($this->getLead());
        }
		return $item;
	}


	/**
	 * @param $url
	 *
	 * @return Upload
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
