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
    static $config_db = array();
    private $config_file_db;
    static $connect_type_db;
            
    function __construct() {
        parent::__construct();
    }
    
    public function LoadConfug() {
        $dir = dirname(filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'), 2)  . "/";
        //Подключение основных файлов ядра
        $this->config_file_db = $dir.'config/db.php';
        
        //echo $this->config_file_db."<br>";
        
        if(isset(\wco\kernel\WCO::$config['kernel_debug'])){
            if(!file_exists($this->config_file_db)){
                throw new \Exception('Не удалось найти файл конфигураций к подключению базе данных ' .$config_file_db);
            }
        }
        include_once($this->config_file_db); //файл конфигурацый
        
        self::$config_db = $config_db;
        
    }
    
    public static function connect($connect_db = null){
        if(is_null($connect_db)){
            self::$connect_type_db = 'default';
        }else{
            self::$connect_type_db = $connect_db;
        }
        
        if(!isset(self::$config_db[self::$connect_type_db])){
            throw new \Exception('Не правильное указано подключение к БД');
        }
        
        try{
            if(self::$config_db[self::$connect_type_db]['db'] == 'mysql'){
                $pdo = new \PDO("mysql:host=".self::$config_db[self::$connect_type_db]['host'].";"
                        . "dbname=".self::$config_db[self::$connect_type_db]['db_name'], 
                        self::$config_db[self::$connect_type_db]['login'], 
                        self::$config_db[self::$connect_type_db]['password']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            
            //sqli
            if(self::$config_db[self::$connect_type_db]['db'] == 'sqlite'){
                $pdo = new \PDO("sqlite:" . self::$config_db[$connect_db]);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA foreign_keys=ON');
            }
            
            //sqli
            if(self::$config_db[self::$connect_type_db]['db'] == 'postgresql'){
                $conStr = sprintf(
                    "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                    self::$config_db[self::$connect_type_db]['host'],
                    self::$config_db[self::$connect_type_db]['port'],
                    self::$config_db[self::$connect_type_db]['db_name'],
                    self::$config_db[self::$connect_type_db]['login'],
                    self::$config_db[self::$connect_type_db]['password']
                );
                $pdo = new \PDO($conStr);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                //$pdo->exec('PRAGMA foreign_keys=ON');
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