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
class Font
{
    const STORE_PATH = '/../../../../fonts';

    static public $override_fonts = array(
        'IPAMincho' => ['name' => 'NotoSerif', 'size_fix' => -1.7],
        'IPAPMincho' => ['name' => 'NotoSerif', 'size_fix' => -1.7],
        'IPAGothic' => ['name' => 'NotoSans', 'size_fix' => -1.7],
        'IPAPGothic' => ['name' => 'NotoSans', 'size_fix' => -1.7],
    );

    /**
     * @var string[]
     */
    static public $installed_builtin_fonts = array();

    static public $builtin_unicode_fonts = array(
        'IPAMincho'  => 'ipam.ttf',
        'IPAPMincho' => 'ipamp.ttf',
        'IPAGothic'  => 'ipag.ttf',
        'IPAPGothic' => 'ipagp.ttf',
        'NotoSans'  => 'NotoSansJP-VF.ttf',
        'NotoSerif' => 'NotoSerifJP-VF.ttf',
    );

    static public $builtin_style_fonts = array(
        'IPAMincho'  => [], // bold, italic
        'IPAPMincho' => [],
        'IPAGothic'  => [],
        'IPAPGothic' => [],
        'NotoSans'  => ['bold'],
        'NotoSerif' => ['bold'],
    );

    static public $builtin_font_aliases = array(
        'Courier New'     => 'Courier',
        'Times New Roman' => 'Times',
    );

    static public function build()
    {
        foreach (array_keys(self::$builtin_unicode_fonts) as $name) {
            self::installBuiltinFont($name);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    static public function getFontSize($names, $size)
    {
        $name = count($names) > 0 ? $names[0] : '';

        if (array_key_exists($name, self::$override_fonts)) {
            $size = $size + self::$override_fonts[$name]['size_fix'];
        }

        return $size;
    }

    /**
     * @param string $name
     * @return string
     */
    static public function getFontName($names)
    {
        $name = count($names) > 0 ? $names[0] : '';

        if (array_key_exists($name, self::$builtin_font_aliases)) {
            return self::$builtin_font_aliases[$name];
        }

        if (array_key_exists($name, self::$override_fonts)) {
            $name = self::$override_fonts[$name]['name'];
        }

        if (array_key_exists($name, self::$builtin_unicode_fonts)) {
            if (self::isInstalledFont($name)) {
                return static::$installed_builtin_fonts[$name];
            } else {
                return self::installBuiltinFont($name);
            }
        }
        return $name;
    }

    /**
     * @param string $name
     * @return string
     * @see http://www.tcpdf.org/doc/code/classTCPDF__FONTS.html
     */
    static public function installBuiltinFont($name, $type='TrueTypeUnicode')
    {
        $filename = self::getBuiltinFontPath($name);

        $font_name = \TCPDF_FONTS::addTTFFont($filename, $type, '', 32);
        static::$installed_builtin_fonts[$name] = $font_name;

        return $font_name;
    }

    /**
     * @param string $name
     * @return boolean
     */
    static public function isInstalledFont($name)
    {
        return array_key_exists($name, static::$installed_builtin_fonts);
    }

    /**
     * @param string $name
     * @return string
     */
    static public function getBuiltinFontPath($name)
    {
        $font_directory = realpath(__DIR__ . self::STORE_PATH);
        return $font_directory . '/' . self::$builtin_unicode_fonts[$name];
    }

    /**
     * @param string $name
     * @return boolean
     */
    static public function isBuiltinUnicodeFont($name, $style)
    {
        if (in_array($name, array_keys(static::$builtin_unicode_fonts))) {
            if (in_array($style, static::$builtin_style_fonts[$name])) {
                return true;
            }
        }
        return false;
    }
}
