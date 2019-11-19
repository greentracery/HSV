<?php
/**
* Преобразование цвета из представления RGB в HSV.
* Color conversion from RGB to HSV presentation.
* @autor A.Mikhaylichenko  greentracery@gmail.com
*/
class HSVRGBConvertor
{
    /**
    * @param string $hex_code - строка цвета в HEX-формате  #AABBCC
    * @return array (
    *  'r' => (int) red, 'g' => (int) green, 'b' => (int) blue
    * )
    * где: r, g, b => [0..255];
    */
    public static function getRGBFromHex($hex_code)
    {
        $hex_code = str_replace('#', '', $hex_code);
        if(!preg_match('/^([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/i', $hex_code))
            throw new Exception('Invalid data format');
        switch(strlen($hex_code))
        {
        case 3:
            $color['r'] = hexdec(substr($hex_code, 0, 1).substr($hex_code, 0, 1)) ;
            $color['g'] = hexdec(substr($hex_code, 1, 1).substr($hex_code, 1, 1)) ;
            $color['b'] = hexdec(substr($hex_code, 2, 1).substr($hex_code, 2, 1)) ;
            break;
        case 6:
            $color['r'] = hexdec(substr($hex_code, 0, 2)) ;
            $color['g'] = hexdec(substr($hex_code, 2, 2)) ;
            $color['b'] = hexdec(substr($hex_code, 4, 2)) ;
    }
        return $color;
    }

    /**
    * @param  array $color (
    *  'r' => (int) red, 'g' => (int) green, 'b' => (int) blue,
    *  прочие поля (если есть) - необяз.
    * )
    * @rerurn array (
    *  'r' => (int) red, 'g' => (int) green, 'b' => (int) blue,
    *  'h' => (int) hue, 's' => (int) saturnation, 'v' => (int) value,
    *  прочие поля (если есть в input) - без изменений
    * )
    * где: r, g, b => [0..255]; h => [0..360]; s, v => [0..100]
    */
    public static function getHSVFromRGB(array $color)
    {
        if(!isset($color['r'], $color['g'], $color['b'])) throw new Exception();
        $R = ($color['r']  / 255); // приводим от [0..255] к [0..1]
        $G = ($color['g'] / 255); // приводим от [0..255] к [0..1]
        $B = ($color['b'] / 255); // приводим от [0..255] к [0..1]

        $max = max($R, $G, $B);
        $min = min($R, $G, $B);

        // V - Value (or B - Brightness) - яркость
        $V = $max;

        // S - Saturation - Насыщенность
        if( 0 == $V )
        {
            $S = 0;
        }
        else
        {
            $S = ($V - $min) / $V;
        }

        // H - Hue - цветовой тон
        if (0 == $S)
        {
            $H = 0; // undefined in HSV representation
        }
        else
        {
            $Hr = ($V - $R) / ($V - $min);
            $Hg = ($V - $G) / ($V - $min);
            $Hb = ($V - $B) / ($V - $min);

            if ($R == $V) $H = $Hb - $Hg;
            if ($G == $V) $H = 2 + $Hr -$Hb;
            if ($B == $V) $H = 4 + $Hg - $Hr;
            $H = $H * 60;
            if( $H < 0 ) $H = $H + 360;
        }

        $color['h'] = round( $H, 0 ); // [0..360]
        $color['s'] = round( ($S * 100), 0); // от [0..1] приводим к [0..100]
        $color['v'] = round( ($V * 100), 0); // от [0..1] приводим к [0..100]

        return $color;
    }
}
