<?php

use Classes\DB;
use Classes\Login;
use Classes\Image;

require_once('classes/DB.php');
require_once('classes/Login.php');
require_once('classes/Image.php');

if(isset($_POST['searchVal'])) {
	$value = htmlspecialchars($_POST['searchVal']);

	if($value !== "") {
		if(DB::_query("SELECT users.username FROM users WHERE users.username LIKE '%".$value."%'")) {
			$usernames = DB::_query("SELECT users.username FROM users WHERE users.username LIKE '%".$value."%' LIMIT 4");
			$address = htmlentities($_SERVER['PHP_SELF']);

			foreach ($usernames as $username) {
				$receiver =  DB::_query('SELECT users.id FROM users WHERE users.username = :username', [ 'username' => $username['username'] ])[0]['id'];
				if(Login::isLogged() != $receiver) {
					if(!Image::hasProfileImage($receiver)) {
						echo '<a href="#" data-id='.$receiver.' data-image="nope" class="list-group-item list-group-item-action searched-user">' . $username['username'] . '</a>';
					} else {
						echo '<a href="#" data-id='.$receiver.' data-image="yep" class="list-group-item list-group-item-action searched-user">' . $username['username'] . '</a>';
					}
				}
			}
		}
	} else {
		echo "";
	}
}

if(isset($_POST['receiver']) && !isset($_POST['messageBody'])) {
	$receiver = htmlspecialchars($_POST['receiver']);

	if($receiver != Login::isLogged()) {
		if(DB::_query('SELECT messages.body, messages.receiver, messages.sender FROM messages WHERE (messages.receiver = :user_id OR messages.sender = :user_id) AND (messages.receiver = :receiver OR messages.sender = :receiver)', [ 'user_id' => Login::isLogged(), 'receiver' => $receiver ])) {
			$messages = DB::_query('SELECT messages.body, messages.receiver, messages.sender FROM messages WHERE (messages.receiver = :user_id OR messages.sender = :user_id) AND (messages.receiver = :receiver OR messages.sender = :receiver)', [ 'user_id' => Login::isLogged(), 'receiver' => $receiver ]);
			foreach ($messages as $message) {
				if($message['sender'] === Login::isLogged()) {
					echo '<div class="bubble-message bubble-message-me"><p>'.$message['body'].'</p></div>';

				} else echo '<div class="bubble-message bubble-message-you"><p>'.$message['body'].'</p></div>';
			}

		}
	}
}

if(isset($_POST['messageBody']) && isset($_POST['user_id'])) {
	$body = htmlspecialchars($_POST['messageBody']);
	
	// The receiver passed from the AJAX request, it is related to the clicked user from the listed ones.
	$receiver = htmlspecialchars($_POST['user_id']);

	// The sender is you.
	$sender = Login::isLogged();

	// Is the receiver an existing user...?
	if(DB::_query('SELECT id from users WHERE id = :receiver', [ 'receiver' => $receiver ])) {
		if($body != "") {
			// If yes, Update DB.
			DB::_query('INSERT INTO messages (receiver, sender, body) VALUES (:r, :s, :body)', [
				'r' 	=> $receiver,
				's' 	=> $sender,
				'body' 	=> $body
			]);

			// Returns to the client-side the body of your message.
			echo '<div class="bubble-message bubble-message-me"><p>'.$body.'</p></div>';
		}
	}
}