<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\Encoding;

class GeslibApiSanitize {
    private $encoding;
    public function __construct()
    {
        $this->encoding = new Encoding();
    }
    public function sanitize_content_array($content_array){
        // Sanitize the array values
        $content_array = array_map(function($value) {
            // Convert numeric strings to numbers
            // Convert string values to compatible encoding
            if (is_string($value)) {
                $value = $this->utf8_encode($value);
            } 
            if (is_numeric($value) && !is_int($value)) {
                $value = floatval(str_replace(',', '.', $value));
            }

            // Remove empty strings
            if ($value === '') {
                $value = null;
            }

            return $value;
        }, $content_array);

        
        return $content_array;
    } 

    /**
    * Convert and Fix UTF8 strings
    *
    * @param $string
    *     String to be fixed
    *
    * Returns
    *     $string
    */
    public function utf8_encode($string) {
        if ($string) {
           return Encoding::fixUTF8( $string );
        } else {
            return NULL;
        }
    }

}