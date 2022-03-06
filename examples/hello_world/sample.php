<?php
require __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

use Thinreports\Report;

$report = new Report(__DIR__ . '/sample.tlf');

$page = $report->addPage();
// text (ivs)
$page->setItemValue('text', '禰󠄀豆子');

// svg
$page->item('image')->setValue(__DIR__ . '/sample.svg')->setSvg(true);

// list
$list = $page->list('default');
$page_cnt = 1;
$total_cnt = 0;
$list->on_header_insert(function ($ipage) {
    $ipage->setItemValue('text', 'AAAAA');
});
$list->on_page_footer_insert(function ($ipage) use (&$page_cnt) {
    $ipage->setItemValue('text', 'Page: '.$page_cnt);
    $page_cnt++;
});
$list->on_footer_insert(function ($ipage) use (&$total_cnt) {
    $ipage->setItemValue('text', 'Total: '.$total_cnt);
});
for($i=0; $i<100; $i++){
    $row = $list->addRow();
    $total_cnt++;
    $row->setItemValue('text1', 'id: '.$total_cnt);
    $row->setItemValue('text2', 'sample value');
}


// next page
$page = $report->addPage();

// text
$page->setItemValue('text', 'Next page');


// generate
$report->generate(__DIR__ . '/sample.pdf');
