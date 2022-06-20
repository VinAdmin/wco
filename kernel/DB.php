<?php
namespace vadc\kernel;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter AS Lam;
use Laminas\Db\Sql\Sql AS SqlLam;
/**
 * Описание класса: Класс подключение к базе данных.
 *
 * @package    Access
 * @subpackage DB
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2016-2021
 * @version 0.1
 * 
 * @property class $_cache Psr16Adapter
 */
class DB extends \vadc\kernel\GenerateSql{
    const VERSION = '0.1';
    private static $_dbname = null;
    private $sql = null;
    public $test = 123;
            
    function __construct() {
        //self::$_dbname = $config_db['mysql']['db_name'];
        parent::__construct();
    }

    public static function connect(){
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
    /**
    public static function ZendConnect(){
        global $config_db;
        $adapter = new Adapter([
            'driver'   => 'Mysqli',
            'hostname' => $config_db['mysql']['host'],
            'database' => $config_db['mysql']['db_name'],
            'username' => $config_db['mysql']['login'],
            'password' => $config_db['mysql']['passwor'],
        ]);
        $sql = new Sql($adapter);
        return $sql;
    }*/
    public static function LaminasConnect(){
        global $config_db;
        $adapter = new Lam([
            'driver'   => 'Mysqli',
            'hostname' => \vadc::$config_db['mysql']['host'],
            'database' => \vadc::$config_db['mysql']['db_name'],
            'username' => \vadc::$config_db['mysql']['login'],
            'password' => \vadc::$config_db['mysql']['passwor'],
        ]);
        $sql = new SqlLam($adapter);
        return $adapter;
    }
//    
//    public function sqlString() {
//        return $this->toString;
//    }
    
    static function Lamf($name) {
        return DB::LaminasConnect()->driver->formatParameterName($name);
    }
}
?>