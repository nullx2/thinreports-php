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

class Layout extends BaseLayout
{
    const FILE_EXT_NAME = 'tlf';
    const COMPATIBLE_VERSION_RANGE_START = '>= 0.9.0';
    const COMPATIBLE_VERSION_RANGE_END   = '< 1.0.0';

    /**
     * @param string $filename
     * @return self
     * @throws Exception\StandardException
     */
    static public function loadFile($filename)
    {
        if (pathinfo($filename, PATHINFO_EXTENSION) != self::FILE_EXT_NAME) {
            $filename .= '.' . self::FILE_EXT_NAME;
        }

        if (!file_exists($filename)) {
            throw new Exception\StandardException('Layout File Not Found', $filename);
        }

        return self::loadData(file_get_contents($filename, true));
    }

    /**
     * @param string $data
     * @return self
     */
    static public function loadData($data)
    {
        $schema = self::parse($data);
        $identifier = md5($data);

        return new self($schema, $identifier);
    }

    /**
     * @access private
     *
     * @param string $file_content
     * @return array
     * @throws Exception\IncompatibleLayout
     */
    static public function parse($file_content)
    {
        $schema = json_decode($file_content, true);

        if (!self::isCompatible($schema['version'])) {
            $rules = array(
                self::COMPATIBLE_VERSION_RANGE_START,
                self::COMPATIBLE_VERSION_RANGE_END
            );
            throw new Exception\IncompatibleLayout($schema['version'], $rules);
        }

        return $schema;
    }

    /**
     * @access private
     *
     * @param string $layout_version
     * @return boolean
     */
    static public function isCompatible($layout_version)
    {
        $rules = array(
            self::COMPATIBLE_VERSION_RANGE_START,
            self::COMPATIBLE_VERSION_RANGE_END
        );

        foreach ($rules as $rule) {
            list($operator, $version) = explode(' ', $rule);

            if (!version_compare($layout_version, $version, $operator)) {
                return false;
            }
        }
        return true;
    }

    protected $identifier;

    /**
     * @param array $schema
     * @param string $identifier
     */
    public function __construct(array $schema, $identifier)
    {
        parent::__construct($schema);
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getReportTitle()
    {
        return $this->schema['title'];
    }

    /**
     * @return string
     */
    public function getPagePaperType()
    {
        return $this->schema['report']['paper-type'];
    }

    /**
     * @return string[]|null
     */
    public function getPageSize()
    {
        if ($this->isUserPaperType()) {
            return array(
              $this->schema['report']['width'],
              $this->schema['report']['height']
            );
        } else {
            return null;
        }
    }

    /**
     * @return boolean
     */
    public function isPortraitPage()
    {
        return $this->schema['report']['orientation'] === 'portrait';
    }

    /**
     * @return boolean
     */
    public function isUserPaperType()
    {
        return $this->schema['report']['paper-type'] === 'user';
    }

    /**
     * @access private
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->schema['version'];
    }

    /**
     * @access private
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
