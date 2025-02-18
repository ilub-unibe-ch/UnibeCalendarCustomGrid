<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\Ctrl;

/**
 * Class Ctrl
 *
 * Provides base functionality which is needed when implementing controller classes in ILIAS using
 * ilCtrl
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
trait CtrlAware
{
    use CtrlHandler;
    use DIC;
    /**
     * @var ICtrlAware
     */
    protected $parent_gui = null;

    /**
     * @throws \ilCtrlException
     */
    public function executeCommand()
    {
        if ($this->handleNextClass($this)) {
            return true;
        }

        $cmd = $this->ctrl()->getCmd();
        if ($this->getActiveTabId()) {
            $this->tabs()->activateTab($this->getActiveTabId());
        }

        //@Todo: Important add permission checks here for various actions.
        if ($this->checkRequestReferenceId()) {
            $this->{$cmd}();
        }
        $this->tpl()->show();

        return true;
    }



    public function getParentController(): ICtrlAware
    {
        return $this->parent_gui;
    }


    public function setParentController(ICtrlAware $ctrlAware)
    {
        $this->parent_gui = $ctrlAware;
    }


    /**
     * @return array of GUI_Class-Names which use CtrlAware
     */
    public function getPossibleNextClasses(): array
    {
        return [];
    }


    /**
     * @return null|string of active Tab
     */
    protected function getActiveTabId(): ?string
    {
        return null;
    }



    public function cancel()
    {
        $this->ctrl()->redirect($this, ICtrlAware::CMD_INDEX);
    }



    protected function setContent(string $html): void
    {
        $this->tpl()->setContent($html);
    }


    protected function setTitle(string $title): void
    {
        $this->tpl()->setTitle($title);
    }


    protected function pushSubTab(int $subtab_id, string $url)
    {
        $this->tabs()->addSubTab($subtab_id, $this->lang()->txt($subtab_id), $url);
    }


    protected function activeSubTab(int $subtab_id)
    {
        $this->tabs()->activateSubTab($subtab_id);
    }


    protected function checkRequestReferenceId(): bool
    {
        $ref_id = $this->getCurrentRefId();
        if ($ref_id) {
            return $this->dic()->access()->checkAccess('read', '', $ref_id);
        }

        return true;
    }
}
