<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Item;

use Thinreports\Item\Style\TextStyle;

class PageNumberItem extends AbstractItem
{
    const TYPE_NAME = 'page-number';

    private $number_format;

    private $translate_x = 0;
    private $translate_y = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct($parent, array $schema)
    {
        parent::__construct($parent, $schema);

        # PageNumberItem is Always dynamically item
        $this->is_dynamic = true;

        $this->style = new TextStyle($schema['style']);
        $this->number_format = $this->schema['format'];
    }

    /**
     * @param string $new_format
     * @return $this
     */
    public function setNumberFormat($new_format)
    {
        $this->number_format = $new_format;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetNumberFormat()
    {
        $this->setNumberFormat($this->schema['format']);
        return $this;
    }

    /**
     * @return string
     */
    public function getNumberFormat()
    {
        return $this->number_format;
    }

    /**
     * @access private
     *
     * @return mixed
     */
    public function getFormattedPageNumber()
    {
        if (!$this->isForReport()) {
            return '';
        }

        $format = $this->getNumberFormat();

        if ($format === '') {
            return '';
        }

        $parent = $this->getParent();
        $report = $parent->getReport();

        return str_replace(
            array('{page}', '{total}'),
            array($parent->getNo(), $report->getLastPageNumber()),
            $format
        );
    }

    /**
     * @access private
     *
     * @return boolean
     */
    public function isForReport()
    {
        return $this->schema['target'] === '' || $this->schema['target'] === 'report';
    }

    /**
     * {@inheritdoc}
     */
    public function getBounds()
    {
        return array(
            'x' => $this->schema['x'] + $this->translate_x,
            'y' => $this->schema['y'] + $this->translate_y,
            'width' => $this->schema['width'],
            'height' => $this->schema['height']
        );
    }

    public function fixBounds($translate_x, $translate_y)
    {
        $this->translate_x = $translate_x;
        $this->translate_y = $translate_y;
    }
}
