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
        return date('Y-m-d H:i:s');
    }
    
    static public function Format($date) {
        $format = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $format->format('H:i:s d-m-Y');
    }
    
    static function formatDateTimeRu($datetime) {
        $arr = [
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
          ];
        $month = date('n', strtotime($datetime))-1;
        
        return date('H:i:s', strtotime($datetime))
                . ' ' . date('d', strtotime($datetime)) 
                .' '. $arr[$month].' '.date('Y', strtotime($datetime));
    }
}
