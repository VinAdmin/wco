<?php
namespace wco\kernel;

/**
 * Description of Date
 *
 * @author vinamin
 */
class Date {
    /**
     * @return string Формат 'Y-m-d'
     */
    static public function Date() {
        return date('Y-m-d');
    }
    
    static public function DateTime() {
        return date('Y-m-d h:m:s');
    }
    
    static public function Format($date) {
        $format = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $format->format('H:i:s d-m-Y');
    }
}
