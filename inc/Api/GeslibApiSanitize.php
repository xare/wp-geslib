<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\Encoding;

class GeslibApiSanitize {
    /**
     * sanitize_content_array
     *
     * @param  mixed $content_array
     * @return mixed $content_array
     */
    public function sanitize_content_array( $content_array ){
        // Sanitize the array values
        $content_array = array_map( function( $value ) {
            // Skip URL strings
            if ( filter_var( $value, FILTER_VALIDATE_URL )) {
                return $value;
            }
            // Convert numeric strings to numbers
            // Convert string values to compatible encoding
            if ( is_string( $value )) {
                $value = $this->utf8_encode( $value );
            }
            if (is_numeric( $value ) && !is_int( $value )) {
                $value = floatval(str_replace(',', '.', $value));
            }

            // Remove empty strings
            if ( $value === '' ) {
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
        $string = ltrim($string, ', ');
        if ($string) {
           return Encoding::fixUTF8( $string );
        } else {
            return NULL;
        }
    }

}