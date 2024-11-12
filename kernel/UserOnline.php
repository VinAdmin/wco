<?php
/**
 * Описание класса: Класс генерации заголовка.
 *
 * @package    NoName
 * @subpackage Heder
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2019
 */

namespace vadc\kernel;

use vadc\kernel\DB;

class UserOnline
{
	private $minut = 5;
	
	function __construct()
	{
		
	}
	
        /**
         * Принимает id пользователя вставляет или обнавля запись таблицы времени
         * присуствия пользователя на сайте.
         * @param type $user_id
         * @return int
         */
	public function UpdateTime($user_id)
	{
		$time = time() + $this->minut * 60;
		
		$stmt = DB::connect()->query("SELECT user_id FROM user_online WHERE user_id = $user_id");
		$result = $stmt->fetch();
		
		if($result['user_id']){
			$stmt = null;
			$query = "UPDATE user_online SET last_time = :last_time WHERE user_id = :user_id";
		}
		else{
			$query = "INSERT INTO user_online (user_id, last_time)
				VALUES(:user_id, :last_time)";
		}
		
		$params = [
				':user_id' => $user_id,
				':last_time'  => $time,
			];
		
		$ins = db::connect()->prepare($query);
		$ins->execute($params);
		$ins = null;
		
		return 1;
	}
	
	public function Time($user_id = null){
		$stmt = DB::connect()->query("SELECT last_time FROM user_online WHERE user_id = $user_id");
		$result = $stmt->fetch();
		$stmt = null;
		return $result['last_time'];
	}
	
	/**
	 * Вывод пользователей в онлайн
	 **/
	public function Online()
	{
		$time = time();
		$stmt = db::connect()->query("SELECT u.login FROM user_online o
			INNER JOIN users u ON u.id = o.user_id
			WHERE o.last_time > $time");
		$result = $stmt->fetchAll();
		$stmt = null;
		return $result;
	}
}
?>