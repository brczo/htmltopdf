# htmltopdf
基于wkhtmltopdf，生成A4、B5等pdf文件。如果你需要生成的pdf是列表式的，有可能还包含表头和表尾，且需要分页，则建议使用本组件。本组件可以根据传入样式计算每页列表数，实现分页。本组件只支持单位px。

# 封装标签对象 
* div 
* header
* p
* table

# 安装方法
```
composer require brczo/htmltopdf
```
使用方法请看src/Test下使用案例
