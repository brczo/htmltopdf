<?php

namespace Brczo\HtmlToPdf;

use Brczo\HtmlToPdf\Exception\FontNotFoundException;

class Font
{
    /**
     * get fonts
     * @return array
     */
    public static function getFonts(): array
    {
        $fontFName = scandir(__DIR__ . '/fonts');
        return array_filter($fontFName, function (&$font) {
            $font = strtoupper($font);
            return $font !== '.' && $font !== '..';
        });
    }

    /**
     * @param string $fontName font name
     * @return bool
     */
    public static function hasFont(string &$fontName): bool
    {
        $fontName = strtoupper($fontName);
        if (!strpos($fontName, '.TTF', -4)) {
            $fontName = $fontName . '.TTF';
        }
        if (!preg_grep('/' . preg_quote($fontName) . '/iu', self::getFonts())) {
            return false;
        }
        return true;
    }

    /**
     * @param array $fontNames
     * @return string
     */
    public static function setFonts(array $fontNames = [])
    {
        if (!$fontNames) {
            $fontNames = Font::getFonts();
        } else {
            foreach ($fontNames as &$fontName) {
                if (!Font::hasFont($fontName)) {
                    throw new FontNotFoundException("font {$fontNames} has not found");
                }
            }
        }
        if (!$fontNames) {
            throw new FontNotFoundException("not fonts!");
        }
        return self::fontCss($fontNames);
    }


    /**
     * @param array $fontNames font names
     * @return string
     */
    private static function fontCss(array $fontNames)
    {
        $fontWeightMap = [
            'EXTRALIGHT' => 200,
            'LIGHT' => 300,
            'NORMAL' => 400,
            'MEDIUM' => 500,
            'BOLD' => 700,
            'HEAVY' => 900
        ];
        $pars = [];
        $optionalFontName = [];
        foreach ($fontNames as $fontName) {
            $arr = explode('-', $fontName);
            $optionalFontName[] = $arr[0];
            $pars[] = $arr[0]; //fontName
            $pars[] = __DIR__ . '/fonts/' . $fontName; //path
            $fontWeight = preg_replace('/\.ttf$/i', '', $arr[1]);
            $pars[] = $fontWeightMap[strtoupper($fontWeight)]; //font weight
        }
        $fontCss = sprintf(str_repeat("@font-face{
					font-family: '%s'; 
					src: url('%s');
					font-weight: %d;
				}", count($fontNames)), ...$pars);
        return $fontCss . "*{font-family: {$optionalFontName[0]};}";
    }

}