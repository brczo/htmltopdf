<?php


namespace Brczo\HtmlToPdf\Tags;


class Div extends Tag
{
    protected $defaultUsedPx = [//refer to https://www.w3.org/TR/CSS21/sample.html
        'margin' => 0,
        'padding' => 0,
        'fontSize' => 16
    ];
    protected $name = 'div';

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
        $this->tag = sprintf("<div %s>%s</div>", $this->getProperty($class, $id), $text);
        return $this;
    }
}