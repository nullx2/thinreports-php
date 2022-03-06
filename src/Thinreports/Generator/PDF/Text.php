<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Generator\PDF;

/**
 * @access private
 */
class Text
{
    static private $pdf_font_style = array(
        'bold'        => 'B',
        'italic'      => 'I',
        'underline'   => 'U',
        'linethrough' => 'D'
    );

    static private $pdf_text_align = array(
        'left'   => 'L',
        'center' => 'C',
        'right'  => 'R'
    );

    static private $pdf_text_valign = array(
        'top'    => 'T',
        'middle' => 'M',
        'bottom' => 'B'
    );

    static private $pdf_default_line_height = 1;

    /**
     * @var \TCPDF
     */
    private $pdf;

    /**
     * @param \TCPDF
     */
    public function __construct(\TCPDF $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @param string $content
     * @param float|string $x
     * @param float|string $y
     * @param float|string $width
     * @param float|string $height
     * @param array $attrs {
     *      @option string "font_family" required
     *      @option string[] "font_style" required
     *      @option float|string "font_size" required
     *      @option string "color" required
     *      @option string "overflow" optional default is "trunecate"
     *      @option boolean "single_row" optional default is false
     *      @option string "align" optional default is "left"
     *      @option string "valign" optional default is "top"
     *      @option string "letter_spacing" optional default is 0
     *      @option string "line_height" optional default is {@see self::$pdf_default_line_height}
     * }
     * @see http://www.tcpdf.org/doc/code/classTCPDF.html
     */
    public function drawTextBox($content, $x, $y, $width, $height, array $attrs = array())
    {
        $styles = $this->buildTextBoxStyles($height, $attrs);

        if ($styles['color'] === null) {
            return;
        }

        $this->setFontStyles($styles);
        $this->pdf->setFontSpacing($styles['letter_spacing']);
        $this->pdf->setCellHeightRatio($styles['line_height']);

        $overflow = $styles['overflow'];
        $prohibited = $styles['prohibited'];

        $font_family = $styles['font_family'];
        $font_styles = $attrs['font_style'];

        $emulating = $this->startStyleEmulation($font_family, $font_styles, $styles, $x, $y, $width, $height);

        $this->pdf->setLastH(0);
        $this->pdf->setSpacesRE("/(?!\\xa0)[\s\p{Z}]/u");
        $this->pdf->MultiCell(
            $width,                  // width
            $height,                 // height
            $content,                // text
            0,                       // border
            $styles['align'],        // align
            false,                   // fill
            1,                       // ln
            $x,                      // x
            $y,                      // y
            false,                   // reset height
            0,                       // stretch mode
            false,                   // is html
            true,                    // autopadding
            $overflow['max_height'], // max-height
            $styles['valign'],       // valign
            $overflow['fit_cell']    // fitcell
        );

        if ($emulating) {
            $this->resetStyleEmulation();
        }
    }

    /**
     * {@see self::drawTextBox}
     */
    public function drawText($content, $x, $y, $width, $height, array $attrs = array())
    {
        $content = str_replace("\n", ' ', $content);
        $attrs['single_row'] = true;

        $this->drawTextBox($content, $x, $y, $width, $height, $attrs);
    }

    /**
     * @param array $style
     */
    public function setFontStyles(array $style)
    {
        $this->pdf->SetFont(
            $style['font_family'],
            $style['font_style'],
            $style['font_size']
        );
        $this->pdf->SetTextColorArray($style['color']);
    }

    /**
     * @param array $attrs
     * @return array
     */
    public function buildTextStyles(array $attrs)
    {
        $font_style = array();

        foreach ($attrs['font_style'] ?: array() as $style) {
            $font_style[] = self::$pdf_font_style[$style];
        }

        if (array_key_exists('line_height', $attrs)) {
            $line_height = $attrs['line_height'];
        } else {
            $line_height = self::$pdf_default_line_height;
        }

        if (array_key_exists('letter_spacing', $attrs)) {
            $letter_spacing = $attrs['letter_spacing'];
        } else {
            $letter_spacing = 0;
        }

        if (array_key_exists('align', $attrs)) {
            $align = $attrs['align'];
        } else {
            $align = 'left';
        }

        if (array_key_exists('valign', $attrs)) {
            $valign = $attrs['valign'];
        } else {
            $valign = 'top';
        }

        if ($attrs['color'] == 'none') {
            $color = null;
        } else {
            $color = ColorParser::parse($attrs['color']);
        }

        return array(
            'font_size'      => Font::getFontSize($attrs['font_family'], $attrs['font_size']),
            'font_family'    => Font::getFontName($attrs['font_family']),
            'font_style'     => implode('', $font_style),
            'color'          => $color,
            'align'          => self::$pdf_text_align[$align],
            'valign'         => self::$pdf_text_valign[$valign],
            'line_height'    => $line_height,
            'letter_spacing' => $letter_spacing
        );
    }

    /**
     * @param float|string $box_height
     * @param array $attrs
     * @return array
     */
    public function buildTextBoxStyles($box_height, array $attrs)
    {
        $is_single = array_key_exists('single_row', $attrs)
                     && $attrs['single_row'] === true;

        if ($is_single) {
            unset($attrs['line_height']);
        }

        if (array_key_exists('overflow', $attrs)) {
            $overflow = $attrs['overflow'];
        } else {
            $overflow = 'truncate';
        }
        switch ($overflow) {
            case 'truncate':
                $fit_cell   = false;
                $max_height = $box_height;
                break;
            case 'fit':
                $fit_cell   = true;
                $max_height = $box_height;
                break;
            case 'expand':
                $fit_cell   = false;
                $max_height = 0;
                break;
        }

        if (array_key_exists('word-wrap', $attrs)) {
            $overflow = $attrs['word-wrap'];
        } else {
            $overflow = 'none';
        }
        switch ($overflow) {
            case 'none':
                $prohibited = false;
                break;
            case 'break-word':
                $prohibited = true;
                break;
        }

        $styles = $this->buildTextStyles($attrs);

        $styles['overflow'] = array(
            'fit_cell'   => $fit_cell,
            'max_height' => $max_height
        );
        $styles['prohibited'] = $prohibited;

        return $styles;
    }

    /**
     * @param string $family
     * @param array $styles
     * @param integer[] $color
     * @return boolean
     */
    public function startStyleEmulation($font_family, array $font_styles, array $styles, int $x, int $y, int $width, int $height)
    {
        $color = $styles['color'];
        $align = $styles['align'];

        $need_bold_emulate = in_array('bold', $font_styles)
                        && !Font::isBuiltinUnicodeFont($font_family, 'bold');
        $need_italic_emulate = in_array('italic', $font_styles)
                        && !Font::isBuiltinUnicodeFont($font_family, 'italic');

        if ($need_bold_emulate || $need_italic_emulate){
            $this->pdf->StartTransform();
        }

        if ($need_bold_emulate) {
            $this->pdf->setDrawColorArray($color);
            $this->pdf->setTextRenderingMode($this->pdf->GetLineWidth() * 0.85);
        }
        if ($need_italic_emulate) {
            switch($align){
                case 'L': // 左端 $y + $height
                    $this->pdf->SkewX(15, $x, $y + $height);
                    break;
                case 'C': // 中央 $y + $height / 2
                    $this->pdf->SkewX(15, $x, $y + $height/2);
                    break;
                case 'R': // 右端 $y
                    $this->pdf->SkewX(15, $x, $y);
                    break;
                default: // 左端扱い
                    $this->pdf->SkewX(15, $x, $y + $height);
            }
        }

        if(!$need_bold_emulate && !$need_italic_emulate) return false;
        return true;
    }

    public function resetStyleEmulation()
    {
        $this->pdf->StopTransform();
        $this->pdf->setTextRenderingMode(0);
    }
}
