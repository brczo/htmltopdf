<?php

namespace Brczo\HtmlToPdf\Tags;

use Brczo\HtmlToPdf\ComputePx;

class Header extends Tag
{
    protected $name = '';
    protected $defaultUsedPx = [ //refer to https://www.w3.org/TR/CSS21/sample.html
        'h1' => [
            'margin' => 10.72,
            'padding' => 0,
            'fontSize' => 32
        ],
        'h2' => [
            'margin' => 12,
            'padding' => 0,
            'fontSize' => 24
        ],
        'h3' => [
            'margin' => 13.28,
            'padding' => 0,
            'fontSize' => 18.72
        ],
        'h4' => [
            'margin' => 17.92,
            'padding' => 0,
            'fontSize' => 16
        ],
        'h5' => [
            'margin' => 24,
            'padding' => 0,
            'fontSize' => 13.28
        ],
        'h6' => [
            'margin' => 26.72,
            'padding' => 0,
            'fontSize' => 12
        ]
    ];

    /**
     * @param string $text
     * @param string $class
     * @param string $id
     * @param string $styles
     * @param int $level
     * @return Tag
     */
    public function set(string $text = '', string $class = '', string $id = '', string $styles = '', int $level = 1): Tag
    {
        $this->name = 'h' . $level;
        $this->styles = $this->setStyles($class, $styles, $id);
        $this->tag = sprintf("<h{$level} %s>%s</h{$level}>", $this->getProperty($class, $id), $text);
        return $this;
    }

    protected function getDefaultUsedFontSize()
    {
        return $this->defaultUsedPx[$this->name]['fontSize'];
    }

    protected function getDefaultUsedMargin()
    {
        return $this->defaultUsedPx[$this->name]['margin'];
    }

    protected function getDefaultUsedPadding()
    {
        return $this->defaultUsedPx[$this->name]['padding'];
    }

    public function getUsedPx()
    {
        return $this->counter->computeUsedPx($this->styles);
    }

}
