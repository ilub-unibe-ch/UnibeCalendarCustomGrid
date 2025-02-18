<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\Ctrl;

use ilLanguage;
use ilTabsGUI;
use Exception;
use ilTree;
use ILIAS\HTTP\Services;
use ilAccessHandler;
use ilObjUser;
use ILIAS\DI\UIServices;
use ilGlobalTemplateInterface;
use ilCtrl;
use ILIAS\DI\Container;
use ilTemplate;

/**
 * Class DIC
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait DIC
{

    private ilAccessHandler $access;
    private ilObjUser $user;
    private ilCtrl $ctrl;
    private ilTemplate $tpl;
    private ilLanguage $language;
    private ilTabsGUI $tabs;


    private function dic(): Container
    {
        return $GLOBALS['DIC'];
    }

    public function ctrl():  ilCtrl
    {
        return $this->dic()->ctrl();
    }


    public function txt(string $variable): string
    {
        return $this->dic()->language()->txt($variable);
    }



    public function tpl():  ilGlobalTemplateInterface
    {
        return $this->dic()->ui()->mainTemplate();
    }


    public function language(): ilLanguage
    {
        return $this->dic()->language();
    }


    public function tabs() : ilTabsGUI
    {
        return $this->dic()->tabs();
    }


    public function ui(): UIServices
    {
        return $this->dic()->ui();
    }


    public function user(): ilObjUser
    {
        return $this->dic()->user();
    }


    public function access(): ilAccessHandler
    {
        return $this->dic()->access();
    }


    public function http(): Services
    {
        return $this->dic()->http();
    }


    public function tree(): ilTree
    {
        return $this->dic()->repositoryTree();
    }


    protected function getCurrentRefId(): int
    {
        try {
            $http = $this->dic()->http();
            var_dump("hello");exit;
            $ref_id = (int) $http->request()->getQueryParams()['ref_id'];
        } catch (Exception $e) {
            $ref_id = (int) $_GET['ref_id'];
        }

        return $ref_id;
    }
}
