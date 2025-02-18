<?php

declare(strict_types=1);

namespace iLub\Plugin\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Item\Renderer as DefaultRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use iLub\Plugin\UnibeCalendarCustomGrid\UI\Item\Upload as DefaultUpload;
use ilUnibeFileHandlerGUI;

/**
 * Class Renderer
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class Renderer extends DefaultRenderer
{
    /**
     * @inheritDoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var $component DefaultUpload
         */
        //Build original standard item
        $f = $this->getUIFactory();
        $item = $f->item()->standard($component->getTitle());
        $item = $component->copyToItem($item);

        $original_rendering = $default_renderer->render($item);
        $handler = new ilUnibeFileHandlerGUI();
        $handler->setObjId($component->getObjectId());
        $file_input = $f->input()->field()->file($handler, $this->txt('files'))->withMaxFiles(20);
        $title = $this->txt('upload'). ': ' .$component->getTitle()->getLabel();

        $dropzone = $f->dropzone()
                        ->file()
                        ->wrapper($title, '#', $f->legacy($original_rendering), $file_input)
        ->withSubmitLabel($this->txt('save'));

        return $default_renderer->render($dropzone);
    }
}
