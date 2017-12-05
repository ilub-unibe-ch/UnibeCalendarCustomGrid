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
		if ($item->getColor()) {
			$clone = $clone->withColor($item->getColor());
		}
		if ($item->getActions()) {
			$clone = $clone->withActions($item->getActions());
		}
		if ($item->getLead()) {
			if($item->getLead() instanceof ILIAS\UI\Component\Image\Image){
				$clone = $clone->withLeadImage($item->getLead());
			}else{
				$clone = $clone->withLeadText($item->getLead());
			}

		} else {
			$clone = $clone->withNoLead();
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
		if ($this->getColor()) {
			$item = $item->withColor($this->getColor());
		}
		if ($this->getActions()) {
			$item = $item->withActions($this->getActions());
		}
		if ($this->getLead()) {
			if($this->getLead() instanceof ILIAS\UI\Component\Image\Image){
				$item = $item->withLeadImage($this->getLead());
			}else{
				$item = $item->withLeadText($this->getLead());
			}
		} else {
			$item = $item->withNoLead();
		}
		return $item;
	}


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
