<?php
/**
 * Описание класса: Главная въюшка.
 *
 * @package    Vadc
 * @subpackage View
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2021
 */
namespace wco\kernel;

use wco\kernel\Heder;
use wco\kernel\WCO;

class View extends Heder
{
    public $views;

    /**
     * Подключение главного шаблона.
     * @global type $config массив конфигураций.
     * @global type $template
     */
    protected function Main()
    {
        global $template;
        
        include_once dirname(WCO::$doc_root).'/domain/'.WCO::gatDomainAlias(WCO::$domain).'/views/main.php';
    }

    function generate($template_view, $array = null)
    {
        if(!empty($array)){
            extract($array);
        }
        ob_start();
        include_once(dirname(WCO::$doc_root).'/domain/'.WCO::gatDomainAlias(WCO::$domain).'/views'.$template_view);
        //include_once('/var/www/wco-full/domain/wco.loc/views/index/index.php');
        $this->views = ob_get_contents();
        ob_end_clean();

        $this->Main();
    }
}
?>