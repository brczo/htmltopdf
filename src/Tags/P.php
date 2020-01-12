<?php


namespace Brczo\HtmlToPdf\Tags;


class P extends Tag
{
    protected $name = 'p';
    protected $defaultUsedPx = [//refer to https://www.w3.org/TR/CSS21/sample.html
        'margin' => 17.92,
        'padding' => 0,
        'fontSize' => 16
    ];


    /**
     * @param string $text
     * @param string $class
     * @param string $styles
     * @param string $id
     * @return Tag
     */
    public function set(string $text = '', string $class = '', string $styles = '', string $id = ''): Tag
    {
        $this->styles = $this->setStyles($class, $styles, $id);
        $this->tag = sprintf("<p %s>%s</p>", $this->getProperty($class, $id), $text);
        return $this;
    }
}