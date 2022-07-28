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
    
    /**
     * Вертикальная ориентация текста в ячейке
     * @param string $param top, bottom, middle
     */
    static public function setValign($param) {
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
}
