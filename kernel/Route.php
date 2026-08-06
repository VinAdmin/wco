<?php
namespace wco\kernel;

use wco\kernel\WCO;

/**
 * Описание класса: Роутер.
 * 
 * @author     Ольхин Виталий <volkhin@texnoblog.uz, ovvitalik@gmail.com>
 * @link       http://texnoblog.uz/
 * @copyright  (C) 2022 - 2026
 * @access public
 * @property string $controller_name Контроллер и действие по умолчанию
 * @property string $action_name Экшен по умолчанию
 * @property string $docRoot Корент деректории
 * @property string $serverUri Пулучаемы адрес строки
 */
class Route
{
    /** @var string Имя контроллера по умолчанию. */
    public $controller_name = 'SiteController';
    /** @var string Имя экшена по умолчанию. */
    private $action_name =  'index';
    /** @var string|null Корень директории (DOCUMENT_ROOT). */
    private $docRoot = null;
    /** @var string|null Адресная строка запроса (REQUEST_URI). */
    private $serverUri = null;
    /** @var string|null GET-параметр option. */
    private $getOption = null;
    /** @var string|null GET-параметр action. */
    private $getAction = null;
    /** @var string|null Путь к файлу контроллера. */
    private $controller_path = null;
    /** @var string Контроллер по умолчанию. */
    private const CONTROLLER_DEFAULT = 'SiteController';
    /** @var string|null Путь к корню директории модулей сайта. */
    public static $link_document = null;
            
    /**
     * Конструктор. Фильтрует входные данные, получает URI и подключает
     * конфигурацию домена.
     */
    function __construct() {
        $this->Filtr();
        $this->getUri();
        if(is_null(self::$link_document)){
            $domain_config = dirname($this->docRoot) . "/domain/".WCO::gatDomainAlias(WCO::$domain)."/config.php";
        } else {
            $domain_config = self::$link_document . "/config.php";
        }
        
        if(file_exists($domain_config)){
            include_once $domain_config;
        }
    }
    
    /**
     * Определяет контроллер и экшен из адресной строки и подключает
     * файл с классом контроллера. Если контроллер не найден,
     * подгружается контроллер по умолчанию.
     * 
     * @return void
     */
    private function loadContriller(): void {
        if($this->getModules() && WCO::$request_uri){
            $uri = preg_split('/\/|\?/', WCO::$request_uri);
            
            if(isset($uri[2])) { $this->controller_name = ucfirst($uri[2]) . 'Controller'; }
            $this->action_name = isset($uri[3]) ? $this->searchUrlValue($uri[3]) : 'index';
        }
        
        // подцепляем файл с классом контроллера
        $this->controller_path = dirname($this->docRoot).'/domain/' 
                . WCO::gatDomainAlias(WCO::$domain).$this->getModules() 
                . "/controllers/" . $this->controller_name . '.php';
        
        //Путь подключения корню директории модулей сайта.
        self::$link_document = dirname($this->docRoot) . "/domain/" 
                . WCO::gatDomainAlias(WCO::$domain) 
                . $this->getModules();
        
        if(file_exists($this->controller_path)){
            include_once $this->controller_path;
            return;
        }
        
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
            if(isset($uri[2])) { $this->action_name = $uri[2]; }
        }

        return;
    }

    /**
     * Запускает контроллер и запрашиваемый экшен контроллера.
     * Если метод экшена не найден, выводит страницу 404.
     * 
     * @return void
     */
    public function run() {
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
            Route::ErrorPage404($controller);
        }
    }

    /**
     * Выводит ошибку «страница не найдена» (HTTP 404).
     * Если у контроллера нет экшена action404, подключается
     * страница по умолчанию.
     * 
     * @param object $controller Объект контроллера.
     * @return void
     */
    private function ErrorPage404($controller) {
        header("HTTP/1.0 404 Not Found");
        $this->action_name = 404;
        $action = 'action'. ucfirst($this->action_name);
        if(method_exists($controller, $action)){
            // вызываем действие контроллера
            $controller->$action();
        }else{
            include_once(dirname($this->docRoot).'/vendor/vinadmin/wco/default_page/nopage.php');
            exit();
        }
    }
    
    /**
     * Получает адрес и обрабатывает запросы для перенаправления.
     * Разбирает URI на контроллер и экшен, определяет модуль.
     * 
     * @return void
     */
    private function getUri() {
        $key_action = null;
        $arr_uri = [];
        
        (string)$uri = \strip_tags($this->serverUri); $pos = [];
        preg_match('(%27)',$uri,$pos);
        
        if($pos == true){
            header('Location: /');
            exit;
        }
        
        if(\preg_match_all('#/([A-Za-z]+)#su', $uri, $arr_uri)){
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
        if(!empty($controller)){
            $this->controller_name = !empty($this->getOption) ? strip_tags($this->getOption) 
                    . 'Controller' : ucfirst($controller).'Controller';
        }
        
        // получаем имя экшена
        if (!empty($action) || !empty($this->getAction)){
            $this->action_name = !empty($this->getAction) ? strip_tags($this->getAction) : $action;
        }
    }
    
    /**
     * Фильтрует входные данные: корень директории, URI запроса
     * и GET-параметры option/action.
     * 
     * @return void
     */
    private function Filtr() {
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->serverUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->getOption = filter_input(INPUT_GET, 'option');
        $this->getAction = filter_input(INPUT_GET, 'action');
    }
    
    /**
     * Подключение модуля к пути контроллера.
     * Проверяет наличие модуля в конфигурации и устанавливает путь
     * к контроллеру модуля (в том числе для модуля ядра wco).
     * 
     * @return string|false Путь к файлу контроллера модуля или false,
     *                      если модуль не найден.
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
     * Парсер ищет первый параметр из адресной строки и возвращает его результат.
     * 
     * @return string|int Имя модуля из адресной строки. Если результат
     *                    ложный, возвращает 0.
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
    
    /**
     * Проверяет наличие папки модуля по первому параметру адресной строки.
     * 
     * @return string|false Путь к папке модуля («/modules/имя») или false,
     *                      если модуль не найден.
     */
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
    
    /**
     * Возвращает значение параметра, если в нём нет знака «=».
     * Иначе возвращает имя экшена по умолчанию.
     * 
     * @param string $param Параметр адресной строки.
     * @return string Значение параметра или «index».
     */
    private function searchUrlValue($param) {
        if (stristr($param, '=') === FALSE) {
            return $param;
        } else {
            return 'index';
        }
    }
}