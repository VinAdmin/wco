<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace vadc\kernel\Grid;

/**
 * Description of TableProperties
 *
 * @author vinamin
 */
class TableProperties {
    static private $valign = 'top';
    /**
     * Вертикальная ориентация текста в ячейке
     * @param string $param top, bottom, middle
     */
    static public function setValign($param) {
        self::$valign = $param;
    }
    
    /**
     * Возвращает вертикальную ориентацию в ячейку.
     * @return string valign
     */
    static protected function getValign() {
        return 'valign="'.self::$valign.'"';
    }
}
