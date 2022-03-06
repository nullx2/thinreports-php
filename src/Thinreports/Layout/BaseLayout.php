<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Layout;

use Thinreports\Exception;
use Thinreports\Item;
use Thinreports\Page\Page;

class BaseLayout
{
    protected $schema;
    protected $named_items;

    /**
     * @param array $schema
     * @param string $identifier
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
        $this->named_items = $this->buildNamedItems($this->getItems());
    }

    /**
     * @access protected
     *
     * @param array $named_items
     * @return array array
     */
    protected function buildNamedItems(array $items)
    {
        $named_items = array();

        foreach ($items as $index => $item) {
            $item_id = $item['id'];

            if ($item_id === '') continue;

            $named_items[$item_id] = $index;
        }

        return $named_items;
    }

    /**
     * @access private
     *
     * @param string $id
     * @return boolean
     */
    public function hasItemById($id)
    {
        return array_key_exists($id, $this->named_items);
    }

    /**
     * @param Page|Section $page
     * @param number $index
     * @return Item\AbstractItem
     * @throws Exception\StandardException
     */
    public function createItem($page, $index)
    {
        $item = $this->getItems()[$index];

        switch ($item['type']) {
            case 'text-block':
                return new Item\TextBlockItem($page, $item);
                break;
            case 'image-block':
                return new Item\ImageBlockItem($page, $item);
                break;
            case 'page-number';
                return new Item\PageNumberItem($page, $item);
                break;
            case 'list':
                return new Item\ListArea($page, $item);
                break;
            default:
                return new Item\BasicItem($page, $item);
                break;
        }
    }

    public function createListByIndex(Page $page, $index)
    {
        $item = $this->getItems()[$index];

        switch ($item['type']) {
            case 'list':
                return new Item\ListArea($page, $item);
                break;
            default:
                throw new Exception\StandardException('This item is not list: ', $index);
                break;
        }
    }

    /**
     * @access protected
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    protected function getItems()
    {
        return $this->schema['items'];
    }

    public function getItemCount()
    {
        return count($this->schema['items']);
    }

    public function getItemIndex($id)
    {
        if (!isset($this->named_items[$id])){
            throw new Exception\StandardException('Item not found: ', $id);
        }
        return $this->named_items[$id];
    }

    protected function getItem($id)
    {
        $index = $this->getItemIndex($id);
        return $this->schema['items'][$index];
    }

    public function getItemIds()
    {
        return array_keys($this->named_items);
    }
}
