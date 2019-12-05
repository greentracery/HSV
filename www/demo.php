<?php
/**
* Пример использования классов HSVRGBConvertor и PaletteHandler.
* An example of using the HSVRGBConvertor and PaletteHandler classes.
* @autor A.Mikhaylichenko  greentracery@gmail.com
* @url https://github.com/greentracery/HSV/
*/

@ini_set("display_errors", "on");

require_once('HSVRGBConvertor.php');
require_once('PaletteHandler.php');

switch (@$_REQUEST['p'])
{
    case '1':
    $palette_table = "palette1.csv"; // sample palette 1 (from http://infotables.ru/avtomobili/27-kody-emalej/797-kody-tsveta-kuzova-audi)
    break;
    case '2':
    $palette_table = "palette2.csv"; // sample palette 2 (from http://infotables.ru/avtomobili/27-kody-emalej/923-kody-tsveta-kuzova-bmv)
    break;
    default:
    $palette_table = "palette3.csv"; // sample palette 3 (from https://colorscheme.ru/color-names.html)
}

function gettestdata($filename){
    $data = array();
    $rowcount = 0;
    $handle = fopen($filename, "r");
    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
        $data[$rowcount]['colorname'] = $row[0];
        $data[$rowcount]['color_code'] = $row[1];
        $rowcount++;
    }
    fclose($handle);
    return $data;
}

$colors = gettestdata($palette_table); // gets test data from file

$ph =  new PaletteHandler();
$colors = $ph->getRGBFromHEX($colors); // gets R, G, B values from HEX code
$colors = $ph->getHSVFromRGB($colors); // Convert RGB to HSV representation model
$colors = $ph->groupColors($colors); // groups palette colours to basic color groups

if (isset($_REQUEST['sort']) ) $ph->assortColors($colors); // sort colours by groups

$basicGroups = $ph->getColorGroups();
$existsGroups = $ph->getExistsGroups($colors);

$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$sort = $uri[0].((count($uri) > 1)? "?{$uri[1]}&sort" : "?sort");
$source1 = highlight_string(file_get_contents("HSVRGBConvertor.php"), true);
$source2 = highlight_string(file_get_contents("PaletteHandler.php"), true);
$out = <<<TMP
<!DOCTYPE html>
<html><head></head><body>
<h3>Базовый набор цветов (цветовые группы):</h3>
<table border = '0'><tr>
TMP;
foreach($basicGroups as $bg) {
$out .= "<td style='background-color: {$bg['hexcode']}; width: 50px;' title='{$bg['name']}'>&nbsp;</td>";
}
$out .= <<<TMP
</tr></table>
<h3>Присутствующие в палитре цветовые группы:</h3>
<table border = '0'><tr>
TMP;
foreach($existsGroups as $eg) {
$out .= "<td style='background-color: {$eg['hexcode']}; width: 50px;' title='{$eg['name']}'>&nbsp;</td>";
}
$out .= <<<TMP
</tr></table>
<h3>Используемая палитра (название, HEX, RGB, HSV, группа):
<a href="{$uri[0]}?p=1">палитра 1</a>&nbsp;|&nbsp;
<a href="{$uri[0]}?p=2">палитра 2</a>&nbsp;|&nbsp;
<a href="{$uri[0]}?p=3">палитра 3</a>
</h3>
<table border = '1'>
<tr>
<th>Название</th>
<th>HEX</th>
<th colspan='3'>RGB</th>
<th>Цвет</th>
<th colspan='3'>HSV</th>
<th><a href="{$sort}">Группа</a></th>
<th>Цвет</th>
TMP;
foreach($colors as $c) {
$out .= <<<TMP
<tr>
<td>{$c['colorname']}</td>
<td>{$c['color_code']}</td>
<td>{$c['r']}</td>
<td>{$c['g']}</td>
<td>{$c['b']}</td>
<td style = 'background-color:{$c['color_code']}; width: 50px;'>&nbsp;</td>
<td>{$c['h']}</td>
<td>{$c['s']}</td>
<td>{$c['v']}</td>
<td>{$c['basename']}</td>
<td style = 'background-color:{$c['hexcode']}; width: 50px;'>&nbsp;</td>
</tr>
TMP;
}

$out .= <<<TMP
</table>
<h3>Исходный код класса HSVRGBConvertor</h3>
<pre>{$source1}</pre>
<h3>Исходный код класса PaletteHandler</h3>
<pre>{$source2}</pre>
</body></html>
TMP;
echo $out;
