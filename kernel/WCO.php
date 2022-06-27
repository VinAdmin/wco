<?php
/**
 * Описание класса: Ядро сайта.
 *
 * @package    Vadc
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2022
 * @link http://texnoblog.uz/
 */

namespace wco\kernel;

use wco\kernel\Route;
use Tracy\Debugger;

class WCO
{
    static $server_name;
    public static $user_id = null;
    private static $file = array();
    public static $_cache = null;
    public static $config = [];
    public static $config_db = [];
    public static $domain = 'default';
    public static $doc_root = null;
    static $request_uri = null;

    public function __construct() 
    {
        $this->LoadConfig();
        if(!isset(self::$config['debug']) || self::$config['debug'] == true){
            Debugger::enable();
            Debugger::$strictMode = true; // display all errors
            Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // all errors except deprecated notices
        }
        
        self::$domain = filter_input(INPUT_SERVER, 'HTTP_HOST');
        self::$doc_root = strip_tags(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'));
        self::$request_uri = strip_tags(filter_input(INPUT_SERVER, 'REQUEST_URI'));
    }
    
    private function LoadConfig() {
        $dir = '../';
        //Подключение основных файлов ядра
        $config_file = $dir.'config/config.php';
        
        if(!file_exists($config_file)){
            throw new \Exception('Не удалось найти файл конфигураций ' .$config_file);
        }else{
            include_once($config_file); //файл конфигурацый
        }
        
        $config_file_db = $dir.'config/db.php';
        if(!file_exists($config_file_db)){
            //throw new \Exception('Не удалось найти файл конфигураций ' .$config_file);
        }else{
            include_once($config_file_db); //конфиг подключения к бд
        }
        
        self::$config = $config;
    }


    /**
     * Экранирует кавычки
     **/
    private function strip_slashes($arr)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = strip_slashes($v);
            } else {
                $arr[$k] = stripslashes($v);
            }
        }
        return $arr;
    }
    
    /**
     * Выводит информацию об авторизированном пользователе.
     * Параметры вытягиваются из $_SESSION['user']
     * @return array
     */
    static public function User()
    {
        if(isset($_SESSION['user'])){
            $user = $_SESSION['user'];
        }
        else{
            $user = 0;
        }
        
        return $user;
    }
    
    /**
     * @return string Login
     */
    static public function Login() {
        if(isset($_SESSION['user'])){
            $login = $_SESSION['user']['login'];
        }
        else{
            $login = 0;
        }
        
        return $login;
    }


    /**
     * Вазввращает имя домена c протаколом.
     *
     * @return string http(s)://hostname.uz/
     **/
    static public function ServerName()
    {
        $host = $_SERVER['HTTP_HOST'];
        return self::$server_name = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' . $host . '/' : 'http://' . $host .'/';
    }
    
    /**
     * Гнератор Url адреса Url($url = '/?option=<имя контроллера>&action=<имя метода>', $params = array())
     * можно и в таком формате Url(url = '/<имя контроллера>/<имя метода>')
     *
     * @param $url = /?option=<имя контроллера>&action=<имя метода>
     * @param $params = arra('params1' => 10, 'params2' => 20)
     *
     * @return string
     **/
    static public function Url(string $url, $params = false)
    {
        $arry1 = array('?option=', 'action=', '&');
        $arry2 = array('', '', '/');
        
        $urlGen = !($params) ? null : '?'.http_build_query($params);
        
        return str_replace($arry1, $arry2, $url).$urlGen;
    }
    
    static public function Redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Подключает файлы стилей css.
     * @param array $files полный путь к файлу
     * @param type $position Поумолчанию head, для расположения файла css в конце 
     * старницы end
     */
    static public function setCss(array $files, $position = 'head'){
        foreach ($files as $row){
            self::$file['css'][$position][] = $row;
        }
    }
    
   /**
    * Подключает файлы скриптов js.
    * @param array $files полный путь к файлу
    * @param type $position Поумолчанию end, для расположения файла css в конце
    * старницы head
    */
    static public function setJs(array $files, $position='end'){
        foreach ($files as $row){
            self::$file['js'][$position][] = $row;
        }
        //var_dump(self::$file);
    }
    
    /**
     * Выыодит подключаемые файлы скриптов и стилей в конце внутренего блока BODY.
     * @return string
     */
    static public function getBodyEnd() {
        $str = "\n";
        if(isset(self::$file['css']['end'])){
            foreach (self::$file['css']['end'] as $row_css){
                $str .= "<link rel=\"stylesheet\" href=\"".$row_css."\">".PHP_EOL;
            }
        }
        if(isset(self::$file['js']['end'])){
            foreach (self::$file['js']['end'] as $row_js){
                $str .= "\t<script src=\"".$row_js."\"></script>".PHP_EOL;
            }
        }
        return $str;
    }
    
    /**
     * Выыодит подключаемые файлы скриптов и стилей в конце внутренего блока Heder.
     * @return string
     */
    static public function getHeder() {
        $str = null;
        if(isset(self::$file['css']['head'])){
            foreach (self::$file['css']['head'] as $row_css){
                $str .= "<link rel=\"stylesheet\" href=\"".$row_css."\">".PHP_EOL;
            }
        }
        if(isset(self::$file['js']['end'])){
            foreach (self::$file['js']['end'] as $row_js){
                $str .= "\t<script src=\"".$row_js."\"></script>".PHP_EOL;
            }
        }
        return $str;
    }
    
    /**
     * Метод выводит в виде массив в удобном для чтения формате 
     * @param type $param
     */
    static public function VarDump($param) {
        echo "<pre>";
        print_r($param);
        echo "</pre>";
    }
    
    /**
     * Запуск роутера
     * @return type
     */
    public function RunKernel() {
        $rout = new Route();
        return $rout->run();
    }
    
    /**
     * Функуия авторизауия пользователя. Добавлена 01.05.2022.
     */
    private function AutUser() {
        $logout = strip_tags(filter_input(INPUT_GET, 'logout'));
        $usetAut = new User();
        //Завершение сесии при выходе из профиля
        if(isset($_GET['logout'])){
            $usetAut->LogOut();
        }
        if(isset($_SESSION['user'])){
            self::$user_id = $_SESSION['user']['id'];
        }
        //Если пользователь авторизировался впускаем на сайт.
        if(isset(self::User()['id'])){
            $user_online = new UserOnline();//Время пробывания пользователя насайте
            if($user_online->Time(vadc::User()['id']) < time()){
                $user_online->UpdateTime($_SESSION['user']['id']);
            }
        }
    }
    
    static function gatDomainAlias($domain = null) {
        if(!isset(self::$config['domain_alias'][$domain])){
            return self::$config['domain_alias']['default'] = 'default';
        }else{
            return self::$config['domain_alias'][$domain];
        }
    }
}