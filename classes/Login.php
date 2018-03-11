<?php 

namespace Classes;

/**
* Handle the login operations.
*
* @author Massimo Piedimonte
*/
class Login
{
	/**
	 * Check whether user is logged in or not by checking the "SNID" cookie;
	 */
	public static function isLogged()
	{
		if(isset($_COOKIE['SNID'])) {
			$token = $_COOKIE['SNID'];

			if(DB::_query('SELECT token FROM login_tokens WHERE token=:token', [ 'token' => sha1($token) ])) {
				$user_id = DB::_query('SELECT user_id FROM login_tokens WHERE token=:token', [ 'token' => sha1($token) ])[0]['user_id'];
				return $user_id;
			}
		}

		return false;
	}
}

?>