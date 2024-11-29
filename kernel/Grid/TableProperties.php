<?php
namespace wco\kernel\Grid;

use wco\kernel\Grid\Filter;

/**
 * Description of TableProperties
 *
 * @author vinamin
 */
class TableProperties extends Filter{
    static private $valign = 'top';
    static private $form;
    static protected $additional_text;
            
    function __construct($column,$aliace) {
        self::setColumnFilter($column, $aliace);
        //var_dump(self::$_column);
        //self::Column($column);
    }
    
    static public function Column($column) {
        //self::$column = $column;
    }


    /**
     * Вертикальная ориентация текста в ячейке
     * @param string $param top, bottom, middle
     */
    static public function setValign($param) {
        //var_dump(self::$_column);
        self::$valign = $param;
        return new Filter();
    }
    
    /**
     * Возвращает вертикальную ориентацию в ячейку.
     * @return string valign
     */
    static protected function getValign() {
        return 'valign="'.self::$valign.'"';
    }
    
    static function setAdditionalText($param) {
        self::$additional_text[self::$key_column] = $param;
    }
}
