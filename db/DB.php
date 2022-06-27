<?php
namespace vco\db;

/**
 * Описание класса: Класс подключение к базе данных.
 *
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2022
 * 
 */
class DB extends \vco\db\GenerateSql{
    private static $_dbname = null;
    private $sql = null;
    public $test = 123;
    static $config_db = array();
            
    function __construct() {
        parent::__construct();
    }
    
    public function LoadConfug() {
        $dir = '../';
        //Подключение основных файлов ядра
        $config_file = $dir.'config/db.php';
        
        if(!file_exists($config_file)){
            throw new \Exception('Не удалось найти файл конфигураций к подключению базе данных ' .$config_file);
        }else{
            include_once($config_file); //файл конфигурацый
        }
        
        self::$config_db = $config_db;
    }
    
    public static function connect($connect_db){
        global $config_db;

        try{
            //return new \PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=".$config_db['dbfile'].";Uid=;Pwd=".$config_db['password'].";");
            if(is_array(\vadc::$config_db['mysql'])){
                $pdo = new \PDO("mysql:dbname=" . \vadc::$config_db['mysql']['db_name'].";"
                        .\vadc::$config_db['mysql']['host'], 
                        \vadc::$config_db['mysql']['login'], 
                        \vadc::$config_db['mysql']['passwor']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                return $pdo;
            }else{
                $pdo = new \PDO("sqlite:" . \vadc::$config_db['dbfile']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA foreign_keys=ON');
                //$pdo->beginTransaction();
                return $pdo;
            }
        }
        catch(PDOException $e){
            $MessageConnect = '<div>Отсуствует подключени</div>';
            $MessageConnect .= '<div>Ошибка:</div>' . $e->getMessage();
            echo $MessageConnect;
            exit();
        }
    }
    
    public function getDb(){
        global $config_db;

        try{
            //return new \PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=".$config_db['dbfile'].";Uid=;Pwd=".$config_db['password'].";");
            $pdo = new \PDO("mysql:dbname=" . \vadc::$config_db['mysql']['db_name'].";".\vadc::$config_db['mysql']['host'], \vadc::$config_db['mysql']['login'], \vadc::$config_db['mysql']['passwor']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
        catch(PDOException $e){
            $MessageConnect = '<div>Отсуствует подключени</div>';
            $MessageConnect .= '<div>Ошибка:</div>' . $e->getMessage();
            echo $MessageConnect;
            exit();
        }
    }
    public static function getLastInsertID(){
        self::connect()->beginTransaction();
        
        return self::connect()->lastInsertId("id");
    }
}
?>