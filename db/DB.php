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
    static $config = array();
            
    function __construct() {
        parent::__construct();
    }
    
    public function LoadConfug() {
        
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