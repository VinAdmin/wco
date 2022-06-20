<?php
/**
 * Описание класса: Главная въюшка.
 *
 * @package    Vadc
 * @subpackage View
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2021
 */
namespace vadc\kernel;

use vadc\kernel\Heder;
use vadc\kernel\Roles;

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
        
        include_once docroot().'/template/'. \vadc::$config['template'].'/main.php';
    }

    function generate($template_view, $array = null)
    {
        if(!empty($array)){
            extract($array);
        }
        ob_start();
        include_once(docroot().'/views'.$template_view);
        $this->views = ob_get_contents();
        ob_end_clean();

        $this->Main();
    }
    
    /**
     * Выводит контет по уникальному ключу $content_id из БД.
     * @param string $content_id
     * @return type
     */
    public function Content(string $content_id)
    {
        $content = DB::connect()
                ->prepare("SELECT text, content_id FROM content"
                        . " WHERE content_id = :content_id");
        $content->execute(['content_id' => $content_id]);
        $res_content = $content->fetch(\PDO::FETCH_ASSOC);
        $content = null;
        $str_content = $res_content['text'];
        if(Roles::setAcces('admin')){
            $str_content .= "<div><a href=\"/content/editor/?content_id=".$res_content['content_id']."\">Редактировать</a></div>";
        }
        return $str_content;
    }
}
?>