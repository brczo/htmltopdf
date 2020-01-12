<?php


namespace Brczo\HtmlToPdf\Test;


use Brczo\HtmlToPdf\Page;
use Brczo\HtmlToPdf\Tags;

class TableTest
{
    private $page;

    public function __construct()
    {
        $this->page = new Page();
        $this->page->debug = true;
    }

    public function createPdf()
    {
        $table = new Tags\Table();
        $thList = ['姓名', '年龄'];
        $tdLists = [['张三', 18], ['李四', 23]];
        $table->pushThList($thList);
        foreach ($tdLists as $tdList) {
            $table->pushTdList($tdList);
        }
        $styles = 'table, table td{border: 1px solid red;}table{width:500px;}';
        $table->set('', '', $styles);
        $page = new Page();
        $page->pushTag($table);
        $page->page(__DIR__ . '/createPdf.pdf');
    }

    public function autoPage()
    {
        $page = $this->page;
//        $page->setOrient('');
//        $page->setFonts(['SourceHanSansCN-Heavy']);

        //div
        $div = new Tags\Div();
        $div->set('测试', '', 'div{font-size:8px;}');
        $page->pushTag($div);

        //header
        $header = new Tags\Header();
        $header->set('头部');
        $page->pushTag($header);

        //table
        $table = new Tags\Table();
        $thList = ['编号', '姓名', '年龄'];
        $tdLists = [];
        for ($i = 0; $i < 320; $i++) {
            $tdLists[] = [$i, '张三', 18];
        }
        $table->pushThList($thList);
        foreach ($tdLists as $tdList) {
            $table->pushTdList($tdList);
        }
        $styles = 'table td{border: 1px solid red;}';
        $table->set('', '', $styles);
        $page->pushTag($table);

        //p
        $p = new Tags\P();
        $p->set('尾部', '', '');
        $page->pushTag($p);

        $page->generate(__DIR__ . '/autoPage.pdf');
    }

    public function headers()
    {
        $page = $this->page;

        //header
        $header = new Tags\Header();
        $page->setFonts(['SourceHanSansCN-Heavy']);
        $header->set('头部', '', '', 'h1{height:300px}');
        $page->pushTag($header);
        $page->pushTag($header);
        $page->pushTag($header);
        $page->pushTag($header);
        $page->pushTag($header);
        $page->pushTag($header);
        $page->wantsPageBreak(true);
        $page->generate(__DIR__ . '/header.pdf');
    }

}
