<?php

namespace Brczo\HtmlToPdf;


use Brczo\HtmlToPdf\Exception\StyleSyntaxException;

class ComputePx
{
    private $margin = 0;
    private $border = 0;
    private $height = 0;
    private $padding = 0;
    private $fontSize = 0;
    private $lineHeight = 0;
    private $lineHeightRatio = 1.2;

    /**
     * @param string $style
     * @return float
     */
    private function margin(string $style): float
    {
        return $this->edge($style, 'margin');
    }

    /**
     * @param string $style
     * @return float
     */
    private function padding(string $style): float
    {
        return $this->edge($style, 'padding');
    }

    /**
     * @param string $style
     * @param string $type
     * @return float
     */
    private function edge(string $style, string $type): float
    {
        $arr = explode(' ', $style);
        $arr = array_filter($arr, function ($item) {
            return $item != '';
        });
        switch (count($arr)) {
            case 1: //单值
            case 2: //双值
                $usedPx = $this->easy($arr[0]) * 2;
                break;
            case 4:
                $usedPx = $this->easy($arr[0]) + $this->easy($arr[2]);
                break;
            default:
                throw new StyleSyntaxException("{$type} syntax error");
                break;
        }
        return $this->{$type} = $usedPx;
    }

    /**
     * @param string $style
     * @return float
     */
    private function border(string $style): float
    {
        return $this->border = $this->easy($style);
    }

    /**
     * @param string $style
     * @return int
     */
    private function height(string $style): int
    {
        return $this->height = $this->easy($style);
    }

    /**
     * @param string $style
     * @return float
     */
    private function lineHeight(string $style): float
    {
        if (preg_match('/[\d\.]+$/i', $style, $matches)) { //ratio
            $this->lineHeightRatio = $this->fontSize * (float)$matches;
        } else {
            $this->lineHeight = $this->easy($style);
        }
    }

    /**
     * @param string $style
     * @return float
     */
    private function fontSize(string $style): float
    {
        return $this->fontSize = $this->easy($style);
    }

    /**
     * @param string $style
     * @return float
     */
    private function easy(string $style): float
    {
        preg_match('/([\.\d]+)\s*px/', $style, $matches);
        return (float)$matches[1];
    }

    /**
     * compute used pixels by styles
     * @param string $styles
     * @return float
     */
    public function computeUsedPx(string $styles): float
    {
        if (strpos($styles, '{')) { //remove brace if exists
            preg_match_all('/\{(.*?)\}/', $styles, $matches);
            if (!$matches) { // if empty in brace
                return $this->margin + $this->fontSize;
            }
            $styles = implode(';', $matches[1]);
        }
        $styles = array_filter(explode(';', $styles), function ($style) { //remove redundant ;
            return trim($style) !== '';
        });
        foreach ($styles as &$style) {
            $arr = explode(':', $style);
            array_walk($arr, function (&$item) {
                $item = trim($item);
            });
            $styleName = trim($arr[0]);
            if (strpos($styleName, '-')) {
                $method = preg_replace_callback('/\-\w/', function ($matches) {
                    return strtoupper(ltrim($matches[0], '-'));
                }, $styleName);
            } else {
                $method = $styleName;
            }
            if (method_exists($this, $method)) {
                $this->{$method}($arr[1]);
            }
            $style = implode(':', $arr);
        }
        if ($this->lineHeight > $this->lineHeightRatio * $this->fontSize) {
            $lineHeight = $this->lineHeight;
        } else {
            $lineHeight = $this->fontSize * $this->lineHeightRatio;
        }
        $height = $lineHeight + $this->padding;
        $height = $height > $this->height ? $height : $this->height;
        return $height + $this->border + $this->margin;
    }

}