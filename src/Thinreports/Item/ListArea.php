<?php

/*
 * This file is part of the Thinreports PHP package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thinreports\Item;

use Thinreports\Page\Section;
use Thinreports\Item\Style\TextStyle;
use Thinreports\Item\TextFormatter;
use Thinreports\Exception;

class ListArea extends AbstractItem //AbstractBlockItem
{
    const TYPE_NAME = 'list-area';

    private $id;
    private $height = 0;
    private $detail_height = 0;
    private $content_height = 0;
    private $auto_page_break = false;

    private $has_header = false;
    private $has_page_footer = false;
    private $has_footer = false;

    // リストデータ
    private $pages = 1;
    private $header_section = array();
    private $detail_sections = array();
    private $page_footer_section = array();
    private $footer_section = array();

    // header/page_footer/footer の callback関数
    private $header_callback_function = null;
    private $page_footer_callback_function = null;
    private $footer_callback_function = null;

    /**
     * Undocumented function
     *
     * @param [type] $parent
     * @param array $schema
     */
    public function __construct($parent, array $schema)
    {
        parent::__construct($parent, $schema);

        $this->height = $schema['height'];
        $this->detail_height = $schema['detail']['height'];
        $this->content_height = $schema['content-height'];
        $this->auto_page_break = $schema['auto-page-break'] === true;

        $this->has_header = $schema['header']['enabled'];
        $this->has_page_footer = $schema['page-footer']['enabled'];
        $this->has_footer = $schema['footer']['enabled'];

        self::initPageSections();
    }

    private function initPageSections()
    {
        if($this->has_header) {
            $this->header_section[$this->pages] = new Section($this->schema['header']);
        }

        if(true) {
            $this->detail_sections[$this->pages] = array();
        }

        if($this->has_page_footer) {
            $this->page_footer_section[$this->pages] = new Section($this->schema['page-footer']);
        }

        if($this->has_footer) {
            $this->footer_section[$this->pages] = new Section($this->schema['footer']);
        }
    }

    public function getPages()
    {
        return $this->pages;
    }

    private function addPage()
    {
        if($this->auto_page_break){
            $this->getParent()->setBreak();
        }
        $this->pages++;
        self::initPageSections();
    }

    public function addRow()
    {
        // 改ページの必要性を算出
        // pattern1: header, detail*N, page_footer, footer 全てが表示出来る
        // -> そのまま追加
        // pattern2: header, detail*N, page_footer 全てが表示出来る
        // -> そのまま追加かつ、改ページ?(改ページ後に行無し)
        // except: 改ページしなければ表示できない
        // -> 改ページ、次ページに行追加

        $pattern1 = $pattern2 = $this->height;

        if ($this->has_header) {
            $pattern1 -= $this->header_section[$this->pages]->getLayout()->getHeight();
            $pattern2 -= $this->header_section[$this->pages]->getLayout()->getHeight();
            // この時点でcontent-heightとは同じ値になっているはず
        }
        if ($this->has_page_footer) {
            $pattern1 -= $this->page_footer_section[$this->pages]->getLayout()->getHeight();
            $pattern2 -= $this->page_footer_section[$this->pages]->getLayout()->getHeight();
        }
        if ($this->has_footer) {
            $pattern1 -= $this->footer_section[$this->pages]->getLayout()->getHeight();
        }

        foreach($this->detail_sections[$this->pages] as $detail_section){
            $pattern1 -= $detail_section->getLayout()->getHeight();
            $pattern2 -= $detail_section->getLayout()->getHeight();
        }

        $new_section = new Section($this->schema['detail']);
        $pattern1 -= $new_section->getLayout()->getHeight();
        $pattern2 -= $new_section->getLayout()->getHeight();

        if ($pattern1 >= 0) {
            // pattern1: そのまま追加可能
            $this->detail_sections[$this->pages][] = $new_section;

        }else if ($pattern2 >= 0) {
            // pattern2: 追加後、改ページ
            $this->detail_sections[$this->pages][] = $new_section;

            if ($this->has_header) {
                if (!empty($this->header_callback_function)) {
                    $func = $this->header_callback_function;
                    $func($this->header_section[$this->pages]);
                }
            }

            if ($this->has_page_footer) {
                if (!empty($this->page_footer_callback_function)) {
                    $func = $this->page_footer_callback_function;
                    $func($this->page_footer_section[$this->pages]);
                }
            }
            $this->addPage(); // ToDo: footerがでかい場合に余白が多くなる可能性

        }else{
            // 改ページしてから、行挿入
            if ($this->has_header) {
                if (!empty($this->header_callback_function)) {
                    $func = $this->header_callback_function;
                    $func($this->header_section[$this->pages]);
                }
            }

            if ($this->has_page_footer) {
                if (!empty($this->page_footer_callback_function)) {
                    $func = $this->page_footer_callback_function;
                    $func($this->page_footer_section[$this->pages]);
                }
            }
            $this->addPage();

            $this->detail_sections[$this->pages][] = $new_section;
        }

        return $new_section;
    }

    public function on_header_insert($callback)
    {
        $this->header_callback_function = $callback;
    }

    public function on_page_footer_insert($callback)
    {
        $this->page_footer_callback_function = $callback;
    }

    public function on_footer_insert($callback)
    {
        $this->footer_callback_function = $callback;
    }

    /**
     * @access private
     *
     * @return Thinreports\Item\AbstractItem[]
     */
    public function getAllItems(int $sub_page=1)
    {
        $items = array();

        $translate_x = 0;
        $translate_y = 0;

        // 2ページ目以降、明細0行の場合はListを表示しない
        $details = isset($this->detail_sections[$sub_page]) ? count($this->detail_sections[$sub_page]) : 0;
        if (isset($this->detail_sections[$sub_page]) ) {

            // ヘッダの出力
            if ($this->has_header) {
                if ($this->pages == $sub_page && !empty($this->header_callback_function)) {
                    // 最終ページ以外は改ページ時に生成済みのため、最終のみここで生成
                    $func = $this->header_callback_function;
                    $func($this->header_section[$this->pages]);
                }
                $items = array_merge($items, $this->header_section[$sub_page]->getAllItems($translate_x, $translate_y));
            }

            // 明細の出力
            $translate_y = 0 - $this->detail_height; // 明細0行の場合、マイナスする
            for ($i=0; $i<$details; $i++) {
                $translate_x = 0;
                $translate_y += $this->detail_height;

                $items = array_merge($items, $this->detail_sections[$sub_page][$i]->getAllItems($translate_x, $translate_y));
            }

            // ページフッターの出力
            if ($this->has_page_footer && $details > 0) {
                if ($this->pages == $sub_page && !empty($this->page_footer_callback_function)) {
                    // 最終ページ以外は改ページ時に生成済みのため、最終のみここで生成
                    $func = $this->page_footer_callback_function;
                    $func($this->page_footer_section[$this->pages]);
                }
                $items = array_merge($items, $this->page_footer_section[$sub_page]->getAllItems($translate_x, $translate_y));
            }
        }

        // フッターの出力(最終ページのみ)
        if ($this->has_footer && $details > 0 && $this->pages == $sub_page) {
            if (!empty($this->footer_callback_function)) {
                $func = $this->footer_callback_function;
                $func($this->footer_section[$this->pages]);
            }
            $items = array_merge($items, $this->footer_section[$sub_page]->getAllItems($translate_x, $translate_y));
        }

        return $items;
    }

    public function getBounds()
    {
        // dummy
    }

    public function fixBounds($translate_x, $translate_y)
    {
        // dummy
    }

}
