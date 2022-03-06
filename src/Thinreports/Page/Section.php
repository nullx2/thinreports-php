<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Page;

use Thinreports\Layout\ListLayout;
use Thinreports\Exception;

class Section
{
    private $layout;
    private $items = array();

    /**
     * @param Report $report
     * @param ListLayout $layout
     * @param integer $page_number
     * @param boolean $countable
     */
    public function __construct($schema)
    {
        $this->layout = new ListLayout($schema);
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
    public function getAllItems($translate_x, $translate_y)
    {
        $translate = $this->layout->getTranslate();

        $count = $this->layout->getItemCount();
        $items = array();

        for($index=0; $index<$count; $index++)
        {
            if(isset($this->items[$index]))
            {
                $item = $this->items[$index];
            }
            else
            {
                $item = $this->layout->createItem($this, $index);
            }

            $item->fixBounds($translate['x'] + $translate_x, $translate['y'] + $translate_y);
            $items[] = $item;
        }

        return $items;
    }
}
