<?php
/**
* Для каждого цвета из заданной палитры цветов определяем его принадлежность
* к одной из базовых групп цветов (на основе HSV модели представления цвета).
* For each color from a given color palette, determine its belonging to one of the
* basic color groups (based on the HSV color representation model).
* @autor A.Mikhaylichenko  greentracery@gmail.com
* @url https://github.com/greentracery/HSV/
*/
class PaletteHandler
{
    // набор групп базовых цветов - basic color groups:
    // произвольный набор цветовых групп, в которые включаются цвета палитры
    // arbitrary set of color groups in which the colors of the palette are included
    private $basicColors = array(
        'black' => array(
            'name' => 'черный',
            'hexcode' => '#000000',
        ),
        'brown' => array(
            'name' => 'коричневый',
            'hexcode' => '#7a400d',
        ),
        'red' => array(
            'name' => 'красный',
            'hexcode' => '#b51c14',
        ),
        'orange' => array(
            'name' => 'оранжевый',
            'hexcode' => '#e88f09',
        ),
        'yellow' => array(
            'name' => 'желтый',
            'hexcode' => '#d2c316',
        ),
        'olive' => array(
            'name' => 'оливковый',
            'hexcode' => '#6A5F31',
        ),
        'green' => array(
            'name' => 'зеленый',
            'hexcode' => '#427e17',
        ),
        'cyan' => array(
            'name' => 'голубой',
            'hexcode' => '#87CEEB',
        ),
        'blue' => array(
            'name' => 'синий',
            'hexcode' => '#2259cf',
        ),
        'violet' => array(
            'name' => 'фиолетовый',
            'hexcode' => '#823CE6'
        ),
        'magenta' => array(
            'name' => 'пурпурный',
            'hexcode' => '#8B008B',
        ),
        'pink' => array(
            'name' => 'розовый',
            'hexcode' => '#FF77FF',
        ),
        'gray' => array(
            'name' => 'серый',
            'hexcode' => '#979c9f',
        ),
        'white' => array(
            'name' => 'белый',
            'hexcode' => '#f1eded',
        ),
    );
    // граничные коэффициенты H, V для каждой из групп - H, V boundary factors for each group:
    // обязательные -  mandatory: Hmin, Hmax => [0..360], basic => keys($this->basicColors)
    // необязательные -  optional: Vmax, Vmin => [0..100]
    // см. так же HSV color wheel diagramm
    private $colorRules = array(
        array( // красный
            'Hmin' => 0,
            'Hmax' => 13,
            'Vmin' => 21,
            'basic' => 'red',
        ),
    array( // коричневый
            'Hmin' => 0,
            'Hmax' => 13,
            'Vmax' => 20,
            'basic' => 'brown',
        ),
        array( // оранжевый
            'Hmin' => 14,
            'Hmax' => 20,
            'Vmin' => 20,
            'basic' => 'orange',
        ),
        array( // коричневый
            'Hmin' => 14,
            'Hmax' => 20,
            'Vmax' => 19,
            'basic' => 'brown',
        ),
        array( // оранжевый
            'Hmin' => 21,
            'Hmax' => 45,
            'Vmin' => 70,
            'basic' => 'orange',
        ),
        array( // коричневый
            'Hmin' => 14,
            'Hmax' => 45,
            'Vmax' => 69,
            'basic' => 'brown',
        ),
        array( // оливковый
            'Hmin' => 46,
            'Hmax' => 74,
            'Vmax' => 60,
            'basic' => 'olive',
        ),
        array( // желтый
            'Hmin' => 46,
            'Hmax' => 74,
            'Vmin' => 61,
            'basic' => 'yellow',
        ),
        array( // зеленый
            'Hmin' => 75,
            'Hmax' => 170,
            'basic' => 'green',
        ),
        array( // зеленый
            'Hmin' => 171,
            'Hmax' => 176,
            'Vmax' => 50,
            'basic' => 'green',
        ),
        array( // голубой
            'Hmin' => 171,
            'Hmax' => 176,
            'Vmin' => 51,
            'basic' => 'cyan',
        ),
        array( // голубой
            'Hmin' => 177,
            'Hmax' => 195,
            'basic' => 'cyan',
        ),
        array( // синий
            'Hmin' => 196,
            'Hmax' => 255,
            'basic' => 'blue',
        ),
        array( // фиолетовый
            'Hmin' => 256,
            'Hmax' => 285,
            'basic' => 'violet',
        ),
        array( // пурпурный
            'Hmin' => 286,
            'Hmax' => 338,
            'Vmax' => 69,
            'basic' => 'magenta',
        ),
        array( // розовый
            'Hmin' => 286,
            'Hmax' => 338,
            'Vmin' => 70,
            'basic' => 'pink',
        ),
        array( // красный
            'Hmin' => 339,
            'Hmax' => 360,
            'Vmin' => 18,
            'basic' => 'red',
        ),
        array( // коричневый
            'Hmin' => 339,
            'Hmax' => 360,
            'Vmax' => 17,
            'basic' => 'brown',
        ),
    );

    private $Vmax_black = 15; // верхняя граница яркости (V) для черного цвета
    private $Vmin_white = 87;  // нижняя граница яркости (V) для белого цвета
    private $RGBdiff_gray = 17; // максимальное значение разности между R, G, B, при котором цвет считается серым
    private $Smax_gray = 15; // максимальное значение насыщенности (S), при котором цвет считается серым

    function __construct(){ }

    /** 
    * @param array $colors - массив, каждый элемент которгого представляет описание цвета в виде
    *  ('colorname' =>"название цвета", 'color_code' => "HEX значение цвета")...
    * @param string $hex_fieldname - имя поля, содержащее hex-код цвета (по умолчанию color_code);
    * @return array (
    *  0 => array(
    *      'colorname' =>"название цвета", 'color_code' => "HEX значение цвета",
    *      'r' => (int) red, 'g' => (int) green, 'b' => (int) blue,
    *      'h' => (int) hue, 's' => (int) saturnation, 'v' => (int) value
    *      ),
    *  1 => array(...),
    *  ...
    * )
    * где: r, g, b => [0..255]; h => [0..360]; s, v => [0..100]
    */
    public function getRGBFromHEX(array $colors, $hex_fieldname = 'color_code')
    {
        foreach($colors as &$row)
        {
            $rgb = HSVRGBConvertor::getRGBFromHex($row[$hex_fieldname]);
            $row += $rgb;
        }
        return $colors;
    }

    /**
    * @param array $colors (
    *  0 => array(
    *      'r' => (int) red, 'g' => (int) green, 'b' => (int) blue - обязательные,
    *      прочие поля (напр.'colorname' =>"название цвета", 'color_code' => "HEX значение цвета") - необяз.
    *      ),
    *  1 => array(...),
    *  ...
    * )
    * @return array (
    *  0 => array(
    *      'r' => (int) red, 'g' => (int) green, 'b' => (int) blue,
    *      'h' => (int) hue, 's' => (int) saturnation, 'v' => (int) value,
    *      прочие поля (если есть в input) - без изменений.
    *      ),
    *  1 => array(...),
    *  ...
    * )
    * где: r, g, b => [0..255]; h => [0..360]; s, v => [0..100]
    */
    public function getHSVFromRGB(array $colors)
    {
        foreach($colors as &$row)
        {
            $hsv = HSVRGBConvertor::getHSVFromRGB($row);
            $row += $hsv;
        }
        return $colors;
    }

    /**
    * @param array $colors  (
    *  0 => array(
    *      'h' => (int) hue, 's' => (int) saturnation, 'v' => (int) value - обязательные,
    *      прочие поля (напр. 'r' => (int) red, 'g' => (int) green, 'b' => (int) blue,
    *          'colorname' =>"название цвета", 'color_code' => "HEX значение цвета") - необяз.
    *      ),
    *  1 => array(...),
    *  ...
    * )
    * @return array (
    *  0 => array(
    *      'h' => (int) hue, 's' => (int) saturnation, 'v' => (int) value,
    *      'basecolor' => (string) группа цветов (red, gray, brown, etc...),
    *      'basename' => (string) название группы (красный, черный, фиолетовый etc...),
    *      'hexcode' => (string) HEX-код основного цвета группы,
    *      прочие поля (если есть в input) - без изменений.
    *      ),
    *  1 => array(...),
    *  ...
    * )
    * где: h => [0..360]; s, v => [0..100]
    */
    public function groupColors(array $colors)
    {
        foreach($colors as &$row)
        {
            if ( !isset($row['h'], $row['s'], $row['v']) ) throw new Exception('Invalid data format');
            foreach($this->colorRules as $color)
            {
                if(!isset($color['Vmin'])) $color['Vmin'] = 0;
                if(!isset($color['Vmax'])) $color['Vmax'] = 100;
                // оценка по диапазону цветового тона (H) и яркости (V):
                if ( ($row['h'] >= $color['Hmin']) && ($row['h'] <= $color['Hmax'])
                && ($row['v'] >= $color['Vmin']) && ($row['v'] <= $color['Vmax']) )
                {
                    $colorclass = $color['basic'];
                }
            }
            // общие правила вне зависимости от цветового тона (Н) и соотношения RGB:
            // для белого/черного/серого разница RGB не более {$RGBdiff_gray}
            // и при насыщенности (S) < {$Smax_gray} цвет считаем оттенком серого:
            if ( (abs($row['r'] - $row['g']) <= $this->RGBdiff_gray && abs($row['r'] - $row['g']) <= $this->RGBdiff_gray
                && abs($row['g'] - $row['b']) <= $this->RGBdiff_gray)  && $row['s'] <= $this->Smax_gray)
            {   // в зависимоти от яркости (V) серый будет восприниматься как:
                if($row['v'] > $this->Vmin_white)
                {
                    $colorclass = 'white'; // 'белый'
                }
                elseif($row['v'] < $this->Vmax_black)
                {
                    $colorclass = 'black'; // 'черный'
                }
                else
                {
                    $colorclass = 'gray'; // 'серый'
                }
            }
            if($row['v'] < $this->Vmax_black) // при яркости (V) < {$Vmax_black} любой цвет воспринимается как
            // черный вне зависимости от H и S:
            {
                $colorclass = 'black';
            }
            if(isset($colorclass))
            {
                $row['basecolor'] = $colorclass;
                $row['basename'] = $this->basicColors[$colorclass]['name'];
                $row['hexcode'] = $this->basicColors[$colorclass]['hexcode'];
            }
        }
        return $colors;
    }

    public function getColorGroups()
    {
        return $this->basicColors;
    }

    /**
    * @param array $colors
    */
    public function getExistsGroups(array $colors)
    {
        $existGroups = array();
        foreach($colors as $row)
        {
            if( isset($row['basecolor']) && in_array( $row['basecolor'], array_keys($this->basicColors) ) ) 
            {
                if(!isset($tmp[$row['basecolor']])) 
                    $tmp[$row['basecolor']] = $this->basicColors[$row['basecolor']];
            }
        }
        // упорядочим имеющиеся группы в том же порядке, как базовые группы
        foreach($this->basicColors as $key => &$value)
        {
            if(isset($tmp[$key])) $existGroups[$key] = $tmp[$key];
        }
        return $existGroups;
    }

    /**
    * @param array $colors
    */
    public function assortColors(array &$colors)
    {
        usort( $colors, function($a, $b){
            return ($a['basecolor'] > $b['basecolor'])? 1 : -1;
            return 0;
        });
    }
}
