<?php
/**
 * Описание класса: Класс логирование событий изменения БД.
 *
 * @package    Logs
 * @subpackage Access
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2019
 */
namespace vadc\kernel;
use vadc\kernel\DB;

class Logs
{
    public function __construct()
    {

    }

    public function SaveLog($log_message)
    {
        $params = [
                ':date_in' => date('Y-m-d H:i:s'),
                ':log_message' => $log_message
        ];

        $query = "INSERT INTO `logs` (`date_in`,`log_message`)
                VALUES (:date_in, :log_message)";

        $ins = db::connect()->prepare($query);
        $ins->execute($params);
    }
    
    public function Logs($limits) {
        $query = DB::connect()->query("SELECT * FROM logs ORDER BY id DESC LIMIT $limits");
        $result = $query->fetchAll();
        $query = null;

        return $result;
    }
}
?>