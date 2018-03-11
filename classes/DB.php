<?php

namespace Classes;

use \PDO;

/**
 * Handle the interaction with the Database.
 *
 * @author Massimo Piedimonte
 */
class DB
{
	/**
	 * Connect to the database.
	 */
	private static function _connect()
	{
		$pdo = new PDO('mysql:host=127.0.0.1;dbname=ph_chat;charset=UTF8', 'root', 'cat');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $pdo;
	}

	/**
	 * Query & Fetch
	 */
	public static function _query($query, $params = [])
	{
		$stmt = self::_connect()->prepare($query);
		$stmt->execute($params);

		if(explode(' ', $query)[0] === 'SELECT') {
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}
	}
}