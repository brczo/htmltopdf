<?php

namespace Brczo\HtmlToPdf;


use Brczo\HtmlToPdf\Tags\Tag;
use Brczo\HtmlToPdf\Tags\Table;
use Brczo\HtmlToPdf\Exception\BeyondLengthException;
use Brczo\HtmlToPdf\Exception\NotFoundCommandException;
use Brczo\HtmlToPdf\Exception\NotSupportedPageException;
use Brczo\HtmlToPdf\Exception\FailedToCreatePdfException;
use Brczo\HtmlToPdf\Exception\NotSupportedOrientException;

class Page
{
    private $pageTypes = [
        'A4' => [210, 297], 'A5' => [148, 210], 'A6' => [105, 148],
        'B5' => [176, 250], 'B6' => [125, 176]
    ];
    private $tags = [];
    private $pageTop = 10; //mm
    private $pageLeft = 10; //mm
    private $pageRight = 10;
    private $pageBottom = 10; //mm
    public $debug = false;
    private $pageType = 'A4';
    private $pathCathe = [];
    private $fontStyles = '';
    private $orient = 'portrait';
    private $minimumFontSize = 12;
    private $pageBreak = false;

    /**
     * you can set you own page type
     * @param string $pageType
     * @param array $size
     * @return $this
     */
    public function setPageType(string $pageType, array $size = [])
    {
        if (!in_array($pageType, array_keys($this->pageTypes), true)) {
            if (!$size) {
                throw new NotSupportedPageException('not supported page type.');
            } else {
                $this->pageTypes[$pageType] = $size;
            }
        }
        $this->pageType = $pageType;
        return $this;
    }

    public function setOrient($orient)
    {
        if (!in_array($orient, ['portrait', 'landscape']))
            throw new NotSupportedOrientException('not supported orient.');
        $this->orient = $orient;
        return $this;
    }

    public function setPageTop(int $top)
    {
        $this->pageTop = $top;
        return $this;
    }

    public function setPageBottom(int $pageBottom)
    {
        $this->pageBottom = $pageBottom;
        return $this;
    }

    public function setFonts(array $fontsName = [])
    {
        $this->fontStyles = Font::setFonts($fontsName);
        return $this;
    }

    public function setPageLeft(int $left)
    {
        $this->pageLeft = $left;
        return $this;
    }

    public function setPageRight(int $right)
    {
        $this->pageRight = $right;
        return $this;
    }

    public function wantsPageBreak(bool $bool)
    {
        $this->pageBreak = $bool;
    }

    public function pushTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function generate(string $filePath)
    {
        if (!$this->tags) return 'no tags!';
        list($styles, $usedPx) = $this->getStylesAndUsedPx();
        $tags = $this->getTags($usedPx);
        $html = sprintf($this->h5(), $styles, $tags);
        if ($this->debug) {
            $pathInfo = pathinfo($filePath);
            file_put_contents($pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.html', $html);
        }
        $command = "echo '{$html}' | {$this->getWkCommand()} - {$filePath} && echo $?";
        $ret = exec($command);
        if ($ret == '0') {
            $this->pathCathe[$this->pageType][$this->orient][] = $filePath;
            return $this;
        } else {
            throw new FailedToCreatePdfException($ret);
        }
    }

    public function setMinimumFontSize(int $fontSize)
    {
        $this->minimumFontSize = $fontSize;
    }

    public function getMinimumFontSize()
    {
        return $this->minimumFontSize;
    }

    private function getStylesAndUsedPx()
    {
        $styles = '';
        $usedPx = 0;
        $styles .= Styles::body();
        $styles .= Styles::textAlign();
        $this->pageBreak && $styles .= Styles::pageBreak();
        if (!$this->fontStyles) {
            $this->setFonts();
        }
        $styles .= $this->fontStyles;
        $styles .= Styles::lineHeight();
        foreach ($this->tags as $tag) {
            if (!$tag instanceof Table) {
                $usedPx += $tag->getUsedPx();
            }
            $styles .= $tag->getStyles();
        }
        return [$styles, $usedPx];
    }

    private function getTags(int $usedPx)
    {
        $page = [];
        $pages = [];
        $table = [];
        $canContainTdEachPage = 0;
        foreach ($this->tags as $i => $tag) {
            if (!$tag instanceof Table) {
                $page[$i] = $tag->getTag();
            } else {
                $table = [$i, $tag];
                $usedPx += $tag->getThPx() * count($tag->getThLists());
                $remainPx = floor($this->mm2px($this->pageTypes[$this->pageType][$this->orient === 'portrait' ? 1 : 0]))
                    - $usedPx - ceil($this->mm2px($this->pageTop)) - ceil($this->mm2px($this->pageBottom));
                if ($remainPx < 0) {
                    throw new BeyondLengthException('beyond length of page');
                }
                $canContainTdEachPage = floor($remainPx / $tag->getTdPx());
                $page[$i] = $tag->getTag($canContainTdEachPage);
            }
        }
        $pages[] = $page;
        while ($table && $table[1]->getTdLists()) {
            $page[$table[0]] = $table[1]->getTag($canContainTdEachPage);
            $pages[] = $page;
        }
        return array_reduce($pages, function ($carry, $item) {
            return $carry .= sprintf("<div class='page'>%s</div>", implode('', $item));
        });
    }

    /**
     * millimeter to px
     * @param int $mm
     * @return float
     */
    public function mm2px(int $mm): float
    {
        $dpi = 96;
        return $mm / 25.4 * $dpi;
    }

    private function getWkCommand()
    {
        $ret = `which wkhtmltopdf`;
        if (!$ret) {
            throw new NotFoundCommandException('not found command wkhtmltopdf.');
        }
        $pageType = $this->pageType;
        return "wkhtmltopdf --page-height {$this->pageTypes[$pageType][1]} --page-width {$this->pageTypes[$pageType][0]} -L {$this->pageLeft} -R {$this->pageRight} -T {$this->pageTop} -B {$this->pageBottom} -O {$this->orient} --disable-smart-shrinking --minimum-font-size {$this->getMinimumFontSize()}";

    }

    /**
     * @return string
     */
    private function h5(): string
    {
        return '<!DOCTYPE html><html lang="ch"><head><meta charset="UTF-8"><title>打印</title></head><style>%s</style><body>%s</body></html>';
    }

}