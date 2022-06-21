<?php
/**
 * Описание класса: Главный контроллер.
 *
 * @package    NoName
 * @subpackage Controller
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2019
 */

namespace wco\kernel;
use wco\kernel\View;

class Controller extends View{
    protected function FilteringFields($filter) {
        $filter = trim($filter);
        $filter = strip_tags($filter);
        $filter = htmlspecialchars($filter);
        return $filter;
    }
    
    protected function FilterInputGet($filter) {
        $filter = filter_input(INPUT_GET, $filter);
        $filter = $this->FilteringFields($filter);
        return $filter;
    }
}
?>