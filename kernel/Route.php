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
        
        $domain_confug = dirname($this->docRoot) . "/domain/".WCO::gatDomainAlias(WCO::$domain)."/config.php";
        if(file_exists($domain_confug)){
            include_once $domain_confug;
        }
    }

    /**
     * Запускает контролле и запрашиваемый экшен контроллера.
     */
    public function run()
    {
        if($this->DefaultPageAuth() || $this->Api()){
            $this->controller_name = ucfirst($this->controller_name);
            //var_dump($this->controller_name);
        } else {
            $this->action_name = 'aut';
        }
        
        // подцепляем файл с классом контроллера
        $this->controller_path = dirname($this->docRoot) . "/domain/" 
                . WCO::gatDomainAlias(WCO::$domain) . "/controllers/" 
                . $this->controller_name.'.php';
        //Путь подключения корню директории модулей сайта.
        self::$link_document = dirname($this->docRoot) . "/domain/" 
                    . WCO::gatDomainAlias(WCO::$domain);
        $this->LoadModules();
        
        //для отладки
        //echo $this->controller_path;
        //echo $this->controller_name;
        //exit();
        //Проверка контроллера
        if(file_exists($this->controller_path)){
            include_once $this->controller_path;
        }
        else{
            /**
             * Если не один контроллер не найден попатка подгрузить контроллер 
             * по по умолчанию.
             */
            $this->ControllerDefaulLoadAction();
            include_once $this->controller_path;
        }
        // создаем контроллер
        $controller = new $this->controller_name;
        $action = 'action'. $this->action_name;
        //var_dump($action);
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
        $key_action = null;
        (string)$uri = \strip_tags($this->serverUri); $pos = [];
        preg_match('(%27)',$uri,$pos);
        if($pos == true){
            header('Location: /');
            exit;
        } $arr_uri = [];
        if(\preg_match_all('#/([a-z]+)#su', $uri, $arr_uri)){
            //var_dump();exit();
            if(!$this->LoadModules(self::ParserUriModules())){
                $controller = ($arr_uri[1][0] != 'index') ? $arr_uri[1][0] : null;
                $key_action = 1;
            }
            if(isset($arr_uri[1][1])){
                $controller = ($arr_uri[1][1] != 'index') ? $arr_uri[1][1] : null;
                $key_action = 2;
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
                    . 'Controller' : $controller.'Controller';
        }
        // получаем имя экшена
        if (!empty($action) || !empty($this->getAction)){
            $this->action_name = !empty($this->getAction) ? strip_tags($this->getAction) : $action;
        }
        //var_dump($this->action_name);
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
     * Используем контролле по умолчанию и загружаем полученый контроллер вместо 
     * экшена.
     * 
     * @return null
     */
    private function ControllerDefaulLoadAction() {
        $this->controller_path = self::$link_document
                        . "/controllers/" . self::CONTROLLER_DEFAULT . '.php';
        $this->action_name = str_replace('Controller', '', $this->controller_name);
        $this->controller_name = self::CONTROLLER_DEFAULT;
        return null;
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
}