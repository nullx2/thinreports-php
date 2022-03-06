<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Generator\Renderer;

use Thinreports\Generator\PDF;

/**
 * @access private
 */
abstract class AbstractRenderer
{
    /**
     * @var PDF\Document
     */
    protected $doc;

    /**
     * @param PDF\Document $doc
     */
    public function __construct(PDF\Document $doc)
    {
        $this->doc = $doc;
    }

    public function normalizeGraphicStyles(array $attrs)
    {
        if (array_key_exists('border-opacity', $attrs)
            && $attrs['border-opacity'] === '0') {
            $stroke_width = 0;
        } else {
            $stroke_width = $attrs['border-width'];
        }

        return array(
            'stroke_color' => $attrs['border-color'],
            'stroke_width' => $stroke_width,
            'stroke_dash'  => $attrs['border-style']
        );
    }

    /**
     * @param array $attrs
     * @return array
     */
    public function buildGraphicStyles(array $attrs)
    {
        if (array_key_exists('border-opacity', $attrs)
            && $attrs['border-opacity'] === '0') {
            $stroke_width = 0;
        } else {
            $stroke_width = $attrs['border-width'];
        }

        return array(
            'stroke_color' => $attrs['border-color'],
            'stroke_width' => $stroke_width,
            'stroke_dash'  => $attrs['border-style'],
            'fill_color'   => $attrs['fill-color']
        );
    }

    /**
     * @param array $attrs
     * @return array
     */
    public function buildTextStyles(array $attrs)
    {
        return array(
            'font_family'    => $attrs['font-family'],
            'font_size'      => $attrs['font-size'],
            'font_style'     => $attrs['font-style'],
            'color'          => $attrs['color'],
            'align'          => $attrs['text-align'],
            'letter_spacing' => $attrs['letter-spacing']
        );
    }

    /**
     * @param string $align
     * @return string
     */
    public function buildTextAlign($align)
    {
        switch ($align) {
            case 'start':
                return 'left';
                break;
            case 'middle':
                return 'center';
                break;
            case 'end':
                return 'right';
                break;
            default:
                return 'left';
        }
    }

    /**
     * @param string|null $valign
     * @return string
     */
    public function buildVerticalAlign($valign)
    {
        return $valign ?: 'top';
    }

    /**
     * @param string|null $letter_spacing
     * @return string|null
     */
    public function buildLetterSpacing($letter_spacing)
    {
        if (in_array($letter_spacing, array(null, 'auto', 'normal'))) {
            return null;
        } else {
            return $letter_spacing;
        }
    }

    /**
     * @param array $attrs
     * @return string
     */
    public function extractBase64Data(array $attrs)
    {
        return $attrs['data']['base64'];
    }
}
