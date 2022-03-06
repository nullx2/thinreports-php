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

class ListLayout extends BaseLayout
{
    /**
     * @return string
     */
    public function getEnabled()
    {
        if(!isset($this->schema['enabled'])) return true; // detail
        return $this->schema['enabled'];
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->schema['height'];
    }

    /**
     * @return string[]|null
     */
    public function getTranslate()
    {
        return array(
            'x' => $this->schema['translate']['x'],
            'y' => $this->schema['translate']['y']
        );
    }
}
