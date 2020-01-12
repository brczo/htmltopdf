<?php

namespace Brczo\HtmlToPdf\Tags;

use Brczo\HtmlToPdf\ComputePx;

abstract class Tag
{
    protected $tag;
    protected $styles;
    protected $counter;
    protected $defaultUsedPx = [];

    public function __construct()
    {
        $this->counter = new ComputePx();
    }

    abstract public function set(): Tag;

    /**
     * id first
     * @param string $class
     * @param string $id
     * @return string
     */
    protected function getProperty(string $class, string $id): string
    {
        return $id ? "id=\"{$id}\"" : ($class ? "class=\"{$class}\"" : '');
    }

    /**
     * id first
     * @param string $class
     * @param string $id
     * @param string $tag
     * @return string
     */
    protected function getSelector(string $class, string $id, string $tag): string
    {
        return $id ? "#{$id}" : ($class ? ".{$class}" : $tag);
    }

    /**
     * @param array $defaultUsedPx
     * @return int
     */
    public function getDefaultUsedPx(array $defaultUsedPx): int
    {
        return array_reduce($defaultUsedPx, function ($carry, $item) {
            return $carry + $item;
        });
    }

    public function getUsedPx()
    {
        return $this->counter->computeUsedPx($this->styles);
    }

    public function getStyles()
    {
        return $this->styles;
    }

    protected function getDefaultUsedFontSize()
    {
        return $this->defaultUsedPx['fontSize'];
    }

    protected function getDefaultUsedMargin()
    {
        return $this->defaultUsedPx['margin'];
    }

    protected function getDefaultUsedPadding()
    {
        return $this->defaultUsedPx['padding'];
    }

    protected function setStyles(string $class, string $styles, string $id, string $name = '')
    {
        $margin = $this->getDefaultUsedMargin();
        $fontSize = $this->getDefaultUsedFontSize();
        $padding = $this->getDefaultUsedPadding();
        $selector = preg_quote($this->getSelector($class, $id, $this->name ?? $name));
        $patternMatch = '/' . $selector . '[a-z;,:\-{\d\s]*%s/i';
        $patternReplace = '/(' . $selector . '[a-z,\s]*\{)/i';
        if (!preg_match('/' . $selector . '[a-z,\s]*\{/i', $styles)) {
            $styles .= $selector . "{font-size:{$fontSize}px;margin:{$margin}px 0}";
        } else {
            if (!preg_match(sprintf($patternMatch, 'font-size'), $styles)) {
                $styles = preg_replace($patternReplace, "$1font-size:{$fontSize}px;", $styles, $limit = 1);
            }
            if (!preg_match(sprintf($patternMatch, 'margin'), $styles)) {
                $styles = preg_replace($patternReplace, "$1margin:{$margin}px 0;", $styles, $limit = 1);
            }
            if (!preg_match(sprintf($patternMatch, 'padding'), $styles)) {
                $styles = preg_replace($patternReplace, "$1padding:{$padding}px 0;", $styles, $limit = 1);
            }
        }

        return $styles;
    }

    public function getTag()
    {
        return $this->tag;
    }

}
