<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Generator;

use Thinreports\Report;
use Thinreports\Layout\Layout;
use Thinreports\Page\Page;
use Thinreports\Generator\Renderer;
use Thinreports\Generator\PDF;
use Thinreports\Item\ListArea;

/**
 * @access private
 */
class PDFGenerator
{
    /**
     * @var Report
     */
    private $report;

    private $doc;

    /**
     * @var Renderer\LayoutRenderer[]
     */
    private $layout_renderers = array();

    /**
     * @var Renderer\ItemRenderer
     */
    private $item_renderer;

    /**
     * @param Report $report
     * @return string
     */
    static public function generate(Report $report)
    {
        $generator = new self($report);
        return $generator->render();
    }

    /**
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->doc = new PDF\Document($report->getDefaultLayout());
        $this->item_renderer = new Renderer\ItemRenderer($this->doc);
    }

    /**
     * @return string
     */
    public function render()
    {
        foreach ($this->report->getPages() as $page) {
            if ($page->isBlank()) {
                $this->doc->addBlankPage();
            } elseif($page->getBreak()) {
                $this->renderPage($page, 1);

                $max_pages = 1;
                foreach($page->getItems() as $id => $item)
                {
                    if(!$item instanceof ListArea) continue;
                    if($item->getPages() > $max_pages){
                        $max_pages = $item->getPages();
                    }
                }
                for($i=2; $i<=$max_pages; $i++){
                    $this->renderPage($page, $i);
                }
            } else {
                $this->renderPage($page);
            }
        }
        return $this->doc->render();
    }

    /**
     * @param Page $page
     */
    public function renderPage(Page $page, int $sub_page=1)
    {
        $layout = $page->getLayout();

        $this->doc->addPage($layout);

        $this->renderItems($page->getAllItems($sub_page));
    }

    /**
     * @param Thinreports\Item\AbstractItem[] $items
     */
    public function renderItems(array $items)
    {
        foreach ($items as $key => $item) {
            $this->item_renderer->render($item);
        }
    }
}
