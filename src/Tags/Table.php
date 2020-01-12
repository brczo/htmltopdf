<?php

namespace Brczo\HtmlToPdf\Tags;

use Brczo\HtmlToPdf\Exception\IncorrectUsageException;
use Brczo\HtmlToPdf\Styles;

class Table extends Tag
{
    private $thLists = [];
    private $tdLists = [];
    private $class = '';
    private $wantsDefaultStyles = true;
    private $id = '';

    protected $defaultUsedPx = [ //td or th
        'margin' => 0,
        'padding' => 0,
        'fontSize' => 16
    ];


    /**
     * @param string $class
     * @param string $id
     * @param string $styles
     * @return Tag
     */
    public function set(string $class = '', string $id = '', string $styles = ''): Tag
    {
        $this->class = $class;
        $this->id = $id;
        $styles = $this->setStyles('', $styles, '', 'td');
        $styles = $this->setStyles('', $styles, '', 'th');
        $this->styles = $this->addSomeCss($styles);
        return $this;
    }

    public function getTag(int $index = 0, bool $wantsThead = true)
    {
        $tds = array_splice($this->tdLists, 0, $index);
        $thead = $this->thLists ? sprintf("<thead>%s</thead>", $this->getTr($this->thLists, 'th')) : '';
        $tbody = $tds ? sprintf("<tbody>%s</tbody>", $this->getTr($tds, 'td')) : '';
        $this->tag = sprintf("<table %s>%s</table>", $this->getProperty($this->class, $this->id), $thead . $tbody);
        return $this->tag;
    }

    /**
     * @param array $lists
     * @param string $type
     * @return string
     */
    private function getTr(array $lists, string $type): string
    {
        $tr = '';
        foreach ($lists as $list) {
            $tr .= sprintf("<tr>%s</tr>", sprintf(str_repeat("<{$type}>%s</{$type}>", count($list)), ...$list));
        }
        return $tr;
    }

    /**
     * @param array $list
     */
    public function pushThList(array $list)
    {
        $this->thLists[] = $list;
    }

    /**
     * @return array
     */
    public function getThLists(): array
    {
        return $this->thLists;
    }

    /**
     * @param array $list
     */
    public function pushTdList(array $list)
    {
        $this->tdLists[] = $list;
    }

    /**
     * @return array
     */
    public function getTdLists(): array
    {
        return $this->tdLists;
    }

    /**
     * @return int
     */
    public function getThPx()
    {
        return $this->usedPxEach($this->styles, 'th');
    }

    /**
     * @return int
     */
    public function getTdPx()
    {
        return $this->usedPxEach($this->styles, 'td');
    }

    public function wantsDefaultStyles(bool $bool)
    {
        $this->wantsDefaultStyles = $bool;
    }

    /**
     * @param string $styles
     * @param string $type
     * @return int|mixed
     */
    private function usedPxEach(string $styles, string $type): int
    {
        preg_match_all("/(?<={$type})\s*\{(.*?)\}/i", $styles, $matches);
        return $this->counter->computeUsedPx(implode(';', $matches[1] ?? []));
    }

    /**
     * add some css
     * @param string $styles
     * @return string|string[]|null
     */
    private function addSomeCss(string $styles)
    {
        $styles .= Styles::tr();
        if ($this->wantsDefaultStyles) {
            if (preg_match('/table\s*\{/i', $styles)) {
                $patternMatch = '/table[a-z:;,\-\s\d{]*%s/i';
                $patternReplace = '/(table\s*\{)/i';
                if (!preg_match(sprintf($patternMatch, 'border-collapse'), $styles)) {
                    $styles = preg_replace($patternReplace, '$1border-collapse: collapse;', $styles, $limit = 1);
                }
                if (!preg_match(sprintf($patternMatch, 'width'), $styles)) {
                    $styles = preg_replace($patternReplace, '$1width:100%;', $styles, $limit = 1);
                }
            } else {
                $styles .= Styles::table();
            }
        }
        $pattern = '/%s[a-z;:\-\s\d,{]*border/i';
        if (!preg_match(sprintf($pattern, 'th'), $styles) && $this->thLists) {
            $styles .= Styles::thBorder();
        }
        if (!preg_match(sprintf($pattern, 'td'), $styles) && $this->tdLists) {
            $styles .= Styles::tdBorder();
        }
        return $styles;
    }

}
