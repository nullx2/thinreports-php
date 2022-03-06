<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Page;

use Thinreports\Report;
use Thinreports\Item\ListArea;
use Thinreports\Layout\Layout;
use Thinreports\Exception;

class Page extends BlankPage
{
    private $report;
    private $layout;
    private $items = array();
    private $break = false;

    /**
     * @param Report $report
     * @param Layout $layout
     * @param integer $page_number
     * @param boolean $countable
     */
    public function __construct(Report $report, Layout $layout, $page_number, $countable = true)
    {
        parent::__construct($page_number, $countable);

        $this->report = $report;
        $this->layout = $layout;
        $this->is_blank = false;
    }

    public function getBreak()
    {
        return $this->break;
    }

    public function setBreak()
    {
        $this->break = true;
    }

    public function copy($page)
    {
        foreach($page->getItems() as $id => $item){
            $copy = clone $item;
            if($copy instanceof ListArea){
                $copy->resetDetails();
            }
            $copy->setParent($this);
            $this->items[$id] = $copy;
        }
    }

    /**
     * @param string $id
     * @return \Thinreports\Item\AbstractItem
     */
    public function item($id)
    {
        $index = $this->layout->getItemIndex($id);

        if (array_key_exists($index, $this->items)) {
            return $this->items[$index];
        }

        $item = $this->layout->createItem($this, $index);
        $this->items[$index] = $item;

        return $item;
    }

    public function list($id = 'default')
    {
        return $this->item($id);
    }

    /**
     * @see self::item()
     */
    public function __invoke($id)
    {
        return $this->item($id);
    }

    /**
     * @param string $id
     * @param mixed $value
     * @throws Exception\StandardException
     */
    public function setItemValue($id, $value)
    {
        $item = $this->item($id);

        $item->setValue($value);
    }

    /**
     * @param array $values
     */
    public function setItemValues(array $values)
    {
        foreach ($values as $id => $value) {
            $this->setItemValue($id, $value);
        }
    }

    /**
     * @param string $id
     * @return boolean
     */
    public function hasItem($id)
    {
        return $this->layout->hasItem($id);
    }

    /**
     * @return string[]
     */
    public function getItemIds()
    {
        return $this->layout->getItemIds();
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * @access private
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @access private
     *
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @access private
     *
     * @return Thinreports\Item\AbstractItem[]
     */
    public function getAllItems(int $sub_page=1)
    {
        $count = $this->layout->getItemCount();
        $items = array();

        for($index=0; $index<$count; $index++)
        {
            if(!isset($this->items[$index]))
            {
                $items[] = $this->layout->createItem($this, $index);
            }
            elseif($this->items[$index]->isTypeOf('list'))
            {
                $items = array_merge($items, $this->items[$index]->getAllItems($sub_page));
            }
            else
            {
                $items[] = $this->items[$index];
            }
        }

        return $items;
    }
}
