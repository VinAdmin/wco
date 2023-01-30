<?php
/**
 * Описание класса: Главная въюшка.
 *
 * @package    Vadc
 * @subpackage View
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2022
 */
namespace wco\kernel;

use wco\kernel\Heder;
use wco\kernel\WCO;

class View extends Heder
{
    public $views;
    private $path_to_domain = null;
    
    function __construct() {
        $this->path_to_domain = dirname(WCO::$doc_root) . '/domain' 
                . WCO::gatDomainAlias(WCO::$domain);
    }

    /**
     * Подключение главного шаблона.
     * @global type $config массив конфигураций.
     * @global type $template
     */
    protected function Main()
    {
        $layout = Route::$link_document . '/views/main.php';
        
        if(WCO::$config['admin_aut_page'] == true){
            $layout = Route::$link_document . '/views/aut.php';
        }
        
        include_once($layout);
    }

    function generate($template_view, $array = null)
    {
        if(!empty($array)){
            extract($array);
        }
        ob_start();
        $views = Route::$link_document . '/views' . $template_view;
        include_once($views);
        $this->views = ob_get_contents();
        ob_end_clean();

        $this->Main();
    }
}
?>