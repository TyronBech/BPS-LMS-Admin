<?php

namespace App\Helpers;

class ColorHelper
{
    
    /**
     * Generates a color palette with the given hex color.
     * The palette consists of 9 shades from 50 (very light) to 950 (very dark)
     * The shades are as follows:
     * - 50: very light
     * - 100: light
     * - 200: semi light
     * - 300: semi dark
     * - 400: dark
     * - 500: the exact color you picked
     * - 600: very dark
     * - 700: good for headers
     * - 800: darker
     * - 900: dark
     * - 950: very dark
     *
     * @param string $hex The hex color to generate the palette with
     * @return array The generated color palette
     */
    public static function generatePalette($hex)
    {
        return [
            50  => self::mix($hex, '#ffffff', 95),
            100 => self::mix($hex, '#ffffff', 90),
            200 => self::mix($hex, '#ffffff', 75),
            300 => self::mix($hex, '#ffffff', 60),
            400 => self::mix($hex, '#ffffff', 30),
            500 => self::hex2rgb($hex),           // The exact color you picked
            600 => self::mix($hex, '#000000', 10),
            700 => self::mix($hex, '#000000', 25), // Good for Headers
            800 => self::mix($hex, '#000000', 45),
            900 => self::mix($hex, '#000000', 65),
            950 => self::mix($hex, '#000000', 85),
        ];
    }

    
    /**
     * Mixes two colors together.
     * The $percentage parameter determines how much of $color2 should be added to $color1.
     * The result is returned as an RGB string (e.g. "255 255 255").
     *
     * @param string $color1 The base color
     * @param string $color2 The color to add to $color1
     * @param int $percentage The percentage of $color2 to add to $color1
     * @return string The mixed color as an RGB string
     */
    private static function mix($color1, $color2, $percentage)
    {
        $rgb1 = self::parseHex($color1);
        $rgb2 = self::parseHex($color2);

        $r = round($rgb1[0] + ($rgb2[0] - $rgb1[0]) * ($percentage / 100));
        $g = round($rgb1[1] + ($rgb2[1] - $rgb1[1]) * ($percentage / 100));
        $b = round($rgb1[2] + ($rgb2[2] - $rgb1[2]) * ($percentage / 100));

        return "$r $g $b";
    }

    
    /**
     * Converts a hex color code to an RGB array.
     * If the given hex code is in the short form (e.g. "#fff"), it will be converted to the long form.
     * The returned array contains the red, green and blue values of the color in the order of [r, g, b]
     * @param string $hex The hex color code to convert
     * @return array The RGB array
     */
    private static function parseHex($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return [$r, $g, $b];
    }
    
    /**
     * Converts a hex color code to an RGB string.
     * The returned string is in the format of "r g b".
     * @param string $hex The hex color code to convert
     * @return string The RGB string
     */
    private static function hex2rgb($hex) {
        $rgb = self::parseHex($hex);
        return "{$rgb[0]} {$rgb[1]} {$rgb[2]}";
    }
}
