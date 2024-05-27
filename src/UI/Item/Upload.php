<?php

declare(strict_types=1);

namespace iLub\Plugin\UnibeCalendarCustomGrid\UI\Item;

use ILIAS\UI\Implementation\Component\Item\Standard;
use ILIAS\UI\Component\Item\Standard as StandardItem;
use ILIAS\UI\Component\Item\Item;

/**
 * Class Upload
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class Upload extends Standard implements StandardItem
{
    protected ?int $obj_id = null;


    public function copyFromItem(Item $item): Upload
    {
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

    public function copyToItem(Item $item): Item
    {
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

    public function withObjectId(int $obj_id): Upload
    {
        $clone = clone $this;
        $clone->obj_id = $obj_id;

        return $clone;
    }

    public function getObjectId(): ?int
    {
        return $this->obj_id;
    }

}
