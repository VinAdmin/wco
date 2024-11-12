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
        
        include_once($layout);
    }

    function generate($template_view, $array = null)
    {
        $usetAut = new \app\models\User();
        
        if(!wco::Login()){
            $aut = $usetAut->Authorisation();
        }
        
        if(isset($_GET['logout'])){
            $usetAut->LogOut();
        }
        
        if(!empty($array)){
            extract($array);
        }
        ob_start();
        
        if(WCO::$config['modules']['admin_aut_page'] == true && !wco::Login()){
            $views = Route::$link_document . '/views/aut.php';
        }else{
            $views = Route::$link_document . '/views' . $template_view;
        }
        
        include_once($views);
        $this->views = ob_get_contents();
        ob_end_clean();

        $this->Main();
    }
}
?>