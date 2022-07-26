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
    protected function FilteringFields($filter,string $allowed = null) {
        $filter = trim($filter);
        $filter = strip_tags($filter, $allowed = null);
        return $filter;
    }
    
    protected function FilterInputGet($filter, $allowed = null) {
        $filter = filter_input(INPUT_GET, $filter);
        $filter = $this->FilteringFields($filter, $allowed);
        return $filter;
    }
    
    protected function FilterInputPost($filter,$allowed = null) {
        $filter = filter_input(INPUT_POST, $filter);
        $filter = $this->FilteringFields($filter, $allowed);
        return $filter;
    }
}
?>