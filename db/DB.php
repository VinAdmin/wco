<?php
namespace wco\db;

/**
 * Описание класса: Класс подключение к базе данных.
 *
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2022
 * 
 */
class DB extends \wco\db\GenerateSql{
    private static $_dbname = null;
    private $sql = null;
    public $test = 123;
    static $config_db;
            
    function __construct() {
        parent::__construct();
    }
    
    private static function LoadConfug() {
        $dir = dirname(__FILE__, 5) . "/";
        //Подключение основных файлов ядра
        $config_file_db = $dir.'config/db.php';
        
        if(!file_exists($config_file_db)){
            throw new \Exception('Не удалось найти файл конфигураций к подключению базе данных ' .$config_file_db);
        }else{
            include_once($config_file_db); //файл конфигурацый
            self::$config_db = $config_db;
        }
    }
    
    public static function connect($connect_db = null){
        self::LoadConfug();
        
        if(is_null($connect_db)){
            $connect_db = 'default';
        }
        
        try{
            if(!isset(self::$config_db[$connect_db])){
                throw new \Exception('Не правильное подключение к БД');
            }
            
            if(self::$config_db[$connect_db]['db'] == 'mysql'){
                $pdo = new \PDO("mysql:dbname=" . self::$config_db[$connect_db]['db_name'].";"
                    . self::$config_db[$connect_db]['host'], 
                        self::$config_db[$connect_db]['login'], 
                        self::$config_db[$connect_db]['password']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            
            if(self::$config_db[$connect_db]['db'] == 'sqlite'){
                $pdo = new \PDO("sqlite:" . self::$config_db[$connect_db]);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA foreign_keys=ON');
            }
            
            return $pdo;
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