<?php
declare(strict_types=1);
namespace iLub\Plugin\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Item\Renderer as DefaultRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use iLub\Plugin\UnibeCalendarCustomGrid\UI\Item\Upload as DefaultUpload;

/**
 * Class Renderer
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class Renderer extends DefaultRenderer {

	/**
	 * @inheritDoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer): string {
		/**
		 * @var $component DefaultUpload
		 */
		//Build original standard item
		$f = $this->getUIFactory();
		$item = $f->item()->standard($component->getTitle());
		$item = $component->copyToItem($item);

		$original_rendering = $default_renderer->render($item);

		$dropzone = $f->dropzone()
		                ->file()
		                ->wrapper($component->getTitle(), $component->getUploadUrl(), $f->legacy($original_rendering))
						->withTitle($this->txt("upload").": ".$component->getTitle()->getLabel());
/*
		$dropzone = $dropzone->withAdditionalOnLoadCode(function($id){
			return "il.Unibe.customizeWrapper($id)";
		});
*/
		$dropzone = $dropzone->withUserDefinedFileNamesEnabled(true);

		return $default_renderer->render($dropzone);
	}
}
