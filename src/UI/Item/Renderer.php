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

		//Build original standard item
		$f = $this->getUIFactory();
		$item = $f->item()->standard($component->getTitle());
		$item = $component->copyToItem($item);

		$original_rendering = $default_renderer->render($item);

		$dropzone = $f->dropzone()
		              ->file()
		              ->wrapper($component->getUploadUrl(), $f->legacy($original_rendering));

		return $default_renderer->render($dropzone);
	}



	/**
	 * @inheritDoc
	 */
	protected function getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks) {
		return new \ilIndependentTemplate('', true, true);
	}
}
