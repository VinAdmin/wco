<?php
namespace wco\kernel;

use wco\kernel\WCO;

/**
 * Описание класса: Роутер.
 * 
 * @author     Ольхин Виталий <volkhin@texnoblog.uz, ovvitalik@gmail.com>
 * @link       http://texnoblog.uz/
 * @copyright  (C) 2020
 * @access public
 * @property string $controller_name Контроллер и действие по умолчанию
 * @property string $action_name Экшен по умолчанию
 * @property string $docRoot Корент деректории
 * @property string $serverUri Пулучаемы адрес строки
 */
class Route
{
    public $controller_name = 'SiteController';
    private $action_name =  'index';
    private $docRoot = null;
    private $serverUri = null;
    private $getOption = null;
    private $getAction = null;
    private $controller_path = null;
            
    function __construct() {
        $this->Filtr();
        $this->getUri();
    }

    /**
     * Запускает контролле и запрашиваемый экшен контроллера.
     */
    public function run()
    {
        if($this->DefaultPageAuth() || $this->Api()){
            $this->controller_name = ucfirst($this->controller_name);
        } else {
            $this->action_name = 'aut';
        }
        
        // подцепляем файл с классом контроллера
        $this->controller_path = dirname($this->docRoot) . "/domain/".WCO::gatDomainAlias(WCO::$domain)."/controllers/" . $this->controller_name.'.php';
        
        //для отладки
        //echo $this->controller_path;
        
        //Проверка контроллера
        if(file_exists($this->controller_path)){
            include_once $this->controller_path;
        }
        else{
            Route::ErrorPage404(1);
        }
        // создаем контроллер
        $controller = new $this->controller_name;
        $action = 'action'. $this->action_name;
        if(method_exists($controller, $action)){
            // вызываем действие контроллера
            $controller->$action();
        }
        else{
            Route::ErrorPage404(2);
        }
    }

    /**
     * Выводит ошибку ненайдена страница.
     */
    private function ErrorPage404($id)
    {
        include_once(dirname($this->docRoot).'/vendor/vinadmin/wco/default_page/nopage.php');
        exit();
    }
    
    public function Api()
    {
        return array_key_exists($this->controller_name, \vadc::$config['modules']);
    }
    /**
     * Получает адрес и обрабатывает запросы для перенаправления.
     */
    private function getUri() {
        (string)$uri = \strip_tags($this->serverUri); $pos = [];
        preg_match('(%27)',$uri,$pos);
        if($pos == true){
            header('Location: /');
            exit;
        } $arr_uri = [];
        if(\preg_match_all('#/([a-z]+)#su', $uri, $arr_uri)){
            //var_dump($arr_uri);
            $controller = ($arr_uri[1][0] != 'index') ? $arr_uri[1][0] : null;
        }else{ $controller = 'Site'; }
        //Если существует массив
        if(isset($arr_uri[1][1])){ $action = end($arr_uri[1]); }
        //Если не пуст получаем имя контроллера
        if(!empty($controller)){
            $this->controller_name = !empty($this->getOption) ? strip_tags($this->getOption) 
                    . 'Controller' : $controller.'Controller';
        }
        // получаем имя экшена
        if (!empty($action) || !empty($this->getAction)){
            $this->action_name = !empty($this->getAction) ? strip_tags($this->getAction) : $action;
        }
    }
    
    private function Filtr() {
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->serverUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->getOption = filter_input(INPUT_GET, 'option');
        $this->getAction = filter_input(INPUT_GET, 'action');
    }
    
    private function DefaultPageAuth() {
        $query_string = filter_input(INPUT_SERVER, 'REQUEST_URI');
        if(!isset(WCO::$config['uri'])){
            throw new \Exception('В конфигурационном файле "config.php" не найден ключ "uri"');
        }
        
        if(!isset(WCO::$config['default_page_auth'])){
            throw new \Exception('В конфигурационном файле "config.php" не найден ключ "default_page_auth"');
        }
        
        if(in_array($query_string, WCO::$config['uri'])){return true;}
        if(WCO::$config['default_page_auth'] === true){
            $auth = isset(\wco::User()['id']) ? true  : false;
        }
        else{
            $auth = true;
        }
        return $auth;
    }
}