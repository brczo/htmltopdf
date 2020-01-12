<?php


namespace Brczo\HtmlToPdf;


class Styles
{
    public static function lineHeight()
    {
        return '*{line-height: 1.2}';
    }

    public static function body()
    {
        return 'body{margin:0;padding:0;}';
    }

    public static function tr()
    {
        return 'tr {page-break-inside: avoid !important;}';
    }

    public static function table()
    {
        return 'table{border-collapse:collapse;width:100%;}';
    }

    public static function thBorder()
    {
        return 'th{border:1px solid;}';
    }

    public static function tdBorder()
    {
        return 'td{border:1px solid;}';
    }

    public static function textAlign()
    {
        return '*{text-align:center;}';
    }

    public static function pageBreak()
    {
        return '.page{page-break-before:auto;page-break-after:always;}';
    }
}