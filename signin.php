<?php

namespace Main;

use Classes\DB;
use Classes\Login;

require_once('classes/DB.php');
require_once('classes/Login.php');
require_once('templates/header.php');

if(isset( $_POST['signin'] )) {
	$email = htmlspecialchars($_POST['email']);
	$password = htmlspecialchars($_POST['password']);

	if(DB::_query('SELECT email FROM users WHERE email=:email', [ 'email' => $email ])) {
		if(password_verify($password, DB::_query('SELECT password FROM users WHERE email=:email', [ 'email' => $email ])[0]['password'])) {
			$cstrong = TRUE;
			$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
			$user_id = DB::_query('SELECT id FROM users WHERE email=:email', [ 'email' => $email ])[0]['id'];

			DB::_query('INSERT INTO login_tokens (user_id, token) VALUES(:user_id, :token)', [ 
				'user_id' 	=> $user_id, 
				'token' 	=> sha1($token) 
			]);

			setcookie('SNID', $token, time() + 60 * 60 * 24 * 7, '/', NULL, NULL, TRUE);
			header('Location: index.php');
		
		} else  echo "<p class='error-msg'>Invalid Credentials.</p>";

	} else echo "<p class='error-msg'>Invalid Credentials.</p>";
	
}

if(!Login::isLogged()) {

?>

<div class="container">
	<div class="col-xs-12">
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" class="signup form-group">
			<h1 class="form-msg">Sign In</h1>

			<div class="form-control">
				<label for="Email" class="sr-only">Email</label>
				<input type="email" class="input-phchat" name="email" placeholder="Email" />
			</div>
			<div class="form-control">
				<input type="password" class="input-phchat" name="password" placeholder="Password" />
			</div>
			<button type="submit" name="signin" class="btn btn-phchat btn-primary">Sign In</button>
		</form>

		<div class="already-have-account">
			<a href="index.php">Don't you have an account?</a>
		</div>
	</div>
</div>

<?php } else header('Location: conversations.php'); ?>

<?php require_once('templates/footer.php'); ?>