<?php

namespace SRAG\Plugins\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Item\Renderer as DefaultRenderer;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * Class Renderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Renderer extends DefaultRenderer {

	/**
	 * @inheritDoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		/**
		 * @var $component \SRAG\Plugins\UnibeCalendarCustomGrid\UI\Item\Upload
		 */
		$f = $this->getUIFactory();

		$item = $this->buildOriginalItem($component);

		$original_rendering = $default_renderer->render($item);

		$dropzone = $f->dropzone()
		              ->file()
		              ->wrapper($component->getUploadUrl(), $f->legacy($original_rendering));

		return $default_renderer->render($dropzone);
	}


	/**
	 * @param \ILIAS\UI\Component\Item\Item $component
	 *
	 * @return \ILIAS\UI\Component\Item\Item
	 */
	protected function buildOriginalItem(Component\Item\Item $component) {
		$f = $this->getUIFactory();

		$item = $f->item()->standard($component->getTitle());

		if ($component->getProperties()) {
			$item = $item->withProperties($component->getProperties());
		}
		if ($component->getDescription()) {
			$item = $item->withDescription($component->getDescription());
		}
		if ($component->getColor()) {
			$item = $item->withColor($component->getColor());
		}
		if ($component->getActions()) {
			$item = $item->withActions($component->getActions());
		}
		if ($component->getLead()) {
			$item = $item->withLeadImage($component->getLead());
		} else {
			$item = $item->withNoLead();
		}

		return $item;
	}


	/**
	 * @inheritDoc
	 */
	protected function getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks) {
		return new \ilIndependentTemplate('', true, true);
	}
}
