<?php
namespace wco\kernel;

use wco\kernel\WCO;

/**
 * Описание класса: Роутер.
 * 
 * @author     Ольхин Виталий <volkhin@texnoblog.uz, ovvitalik@gmail.com>
 * @link       http://texnoblog.uz/
 * @copyright  (C) 2022
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
    private const CONTROLLER_DEFAULT = 'SiteController';
    public static $link_document = null;
            
    function __construct() {
        $this->Filtr();
        $this->getUri();
        if(is_null(self::$link_document)){
            $domain_confug = dirname($this->docRoot) . "/domain/".WCO::gatDomainAlias(WCO::$domain)."/config.php";
        } else {
            $domain_confug = self::$link_document . "/config.php";
        }
        
        if(file_exists($domain_confug)){
            include_once $domain_confug;
        }
    }
    
    private function loadContriller() {
        if($this->getModules() && WCO::$request_uri){
            $uri = preg_split('/\/|\?/', WCO::$request_uri);
            if(isset($uri[2])) { $this->controller_name = ucfirst($uri[2]).'Controller';}
            $this->action_name = isset($uri[3]) ? $this->searchUrlValue($uri[3]) : 'index';
        }
        
        // подцепляем файл с классом контроллера
        $this->controller_path = dirname($this->docRoot).'/domain/'.WCO::gatDomainAlias(WCO::$domain).$this->getModules() . "/controllers/" 
                . $this->controller_name.'.php';
        
        //Путь подключения корню директории модулей сайта.
        self::$link_document = dirname($this->docRoot) . "/domain/" 
                    . WCO::gatDomainAlias(WCO::$domain).$this->getModules();
        
        //var_dump($this->controller_path);exit();
        //Проверка контроллера
        if(file_exists($this->controller_path)){ //Емли контроллер не существует используем по умолчанию
            include_once $this->controller_path;
        }
        else{
            /**
             * Если не один контроллер не найден попытка подгрузить контроллер 
             * по умолчанию.
             */
            $this->controller_name = self::CONTROLLER_DEFAULT;
            
            $this->controller_path = dirname($this->docRoot).'/domain/'.WCO::gatDomainAlias(WCO::$domain).$this->getModules() . "/controllers/" 
                . $this->controller_name. '.php';
            include_once $this->controller_path;
            if(WCO::$request_uri){
                $uri = preg_split('/\/|\?/', WCO::$request_uri);
                if(isset($uri[1])) { $this->action_name = $uri[1]; }
            }
            
            if($this->getModules() && WCO::$request_uri){
                $uri = preg_split('/\/|\?/', WCO::$request_uri);
                if(isset($uri[2])) { $this->action_name = $uri[2];}
            }
            
            //var_dump($this->action_name);exit();
        }
    }

    /**
     * Запускает контролле и запрашиваемый экшен контроллера.
     */
    public function run()
    {
        $this->loadContriller();
        // создаем контроллер
        $controller = new $this->controller_name;
        $action = 'action'. ucfirst($this->action_name);
        //var_dump($action);exit();
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
    
    /**
     * Получает адрес и обрабатывает запросы для перенаправления.
     */
    private function getUri() {
        $key_action = null;
        (string)$uri = \strip_tags($this->serverUri); $pos = [];
        preg_match('(%27)',$uri,$pos);
        if($pos == true){
            header('Location: /');
            exit;
        } $arr_uri = [];
        if(\preg_match_all('#/([a-z]+)#su', $uri, $arr_uri)){
            //var_dump($arr_uri);exit();
            if(!$this->LoadModules(self::ParserUriModules())){
                $controller = ($arr_uri[1][0] != 'index') ? $arr_uri[1][0] : null;
                $key_action = 1;
            }
            if(isset($arr_uri[1][1])){
                $controller = ($arr_uri[1][0] != 'index') ? $arr_uri[1][0] : null;
                $key_action = 1;
            }
        }else{ $controller = 'Site'; }
        //Если существует массив
        if(isset($arr_uri[1][$key_action])){
            $action = end($arr_uri[1]);
        }
        //Если не пуст получаем имя контроллера
        //var_dump($controller);
        if(!empty($controller)){
            $this->controller_name = !empty($this->getOption) ? strip_tags($this->getOption) 
                    . 'Controller' : ucfirst($controller).'Controller';
        }
        // получаем имя экшена
        if (!empty($action) || !empty($this->getAction)){
            $this->action_name = !empty($this->getAction) ? strip_tags($this->getAction) : $action;
        }
        //var_dump($this->controller_name);
    }
    
    private function Filtr() {
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->serverUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->getOption = filter_input(INPUT_GET, 'option');
        $this->getAction = filter_input(INPUT_GET, 'action');
    }
    
    /**
     * Подключение модуля к пути контроллера.
     * @return string
     */
    private function LoadModules() {
        //Доступ к контролеру ядра.
        WCO::$config['modules']['wco'] = 'vendor/vinadmin/wco';
        //Проверяем ключ массива.
        if(isset(WCO::$config['modules'][self::ParserUriModules()])){
            if(self::ParserUriModules() == 'wco'){
                self::$link_document = dirname($this->docRoot) .'/'. WCO::$config['modules'][self::ParserUriModules()];
                $modules = self::$link_document . '/controllers/' . $this->controller_name.'.php';
                $this->controller_path = $modules;
                //var_dump($modules);
                return $modules;
            }
            self::$link_document = dirname($this->docRoot) . "/domain/" 
                    . WCO::gatDomainAlias(WCO::$domain) . '/' 
                    . WCO::$config['modules'][self::ParserUriModules()];
            
            $modules = self::$link_document . "/controllers/" 
                    . $this->controller_name.'.php';
            if(file_exists($modules)){
                $this->controller_path = $modules;
            }else{
                $this->controller_path = self::$link_document
                        . "/controllers/" . self::CONTROLLER_DEFAULT . '.php';
                $this->controller_name = self::CONTROLLER_DEFAULT;
            }
            //var_dump($this->controller_path);exit();
            return $modules;
        }
        return false;
    }
    
    /**
     * Порсер ищит прервый параметр из адресной сторики и возвращает его результат.
     * 
     * @return string Если результат ложный возвращает 0.
     */
    static function ParserUriModules() {
        if(WCO::$request_uri){
            $uri = preg_split('/\/|\?/', WCO::$request_uri);
            //var_dump($uri);
            if(isset($uri[1])){
                return $uri[1];
            }
        }
        
        return 0;
    }
    
    private function getModules() {
        if(WCO::$request_uri){
            $uri = preg_split('/\/|\?/', WCO::$request_uri);
            if(!isset($uri[1])){ return false; }
            if(empty($uri[1])){ return false; }
            $modulfolder = dirname($this->docRoot) . "/domain/" 
                . WCO::gatDomainAlias(WCO::$domain) . '/modules/' . $uri[1];
            if(is_dir($modulfolder)){
                return '/modules/' . $uri[1];
            }
        }
        
        return false;
    }
    
    private function searchUrlValue($param) {
        if (stristr($param, '=') === FALSE) {
            return $param;
        } else {
            return 'index';
        }
    }
}