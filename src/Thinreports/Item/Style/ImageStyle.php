<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Item\Style;

/**
 * @access private
 */
class ImageStyle extends BasicStyle
{
    static protected $available_style_names = array(
        'position_x',
        'position_y'
    );

    /**
     * @param string $alignment
     */
    public function set_position_x($alignment)
    {
        $this->verifyStyleValue('align', $alignment, array('left', 'center', 'right'));
        $this->styles['position_x'] = $alignment;
    }

    /**
     * @return string
     */
    public function get_position_x()
    {
        $alignment = $this->readStyle('position_x');
        return $alignment === '' ? 'left' : $alignment;
    }

    /**
     * @param string $alignment
     */
    public function set_posotion_y($alignment)
    {
        $this->verifyStyleValue('valign', $alignment, array('top', 'middle', 'bottom'));
        $this->styles['posotion_y'] = $alignment;
    }

    /**
     * @return string
     */
    public function get_posotion_y()
    {
        $alignment = $this->readStyle('posotion_y');
        return $alignment === '' ? 'top' : $alignment;
    }
}
