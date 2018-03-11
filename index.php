<?php

namespace Main;

use Classes\DB;
use Classes\Login;

require_once('classes/DB.php');
require_once('classes/Login.php');
require_once('templates/header.php');

// Registration of the user.
if(isset($_POST['signup'])) {
	$username = htmlspecialchars($_POST['username']);
	$email = htmlspecialchars($_POST['email']);
	$password = htmlspecialchars($_POST['password']);

	// Is username already taken?
	if(!DB::_query('SELECT id FROM users WHERE username=:username', [ 'username' => $username ])) {
		// Is email already taken?
		if(!DB::_query('SELECT id FROM users WHERE email=:email', [ 'email' => $email ])) {
			// Is length of username valid?
			if(strlen($username) >= 3 && strlen($username) < 30) {
				// Is length of password valid?
				if(strlen($password) >= 3 && strlen($password) < 30) {
					// Does username contain valid characters?
					if(preg_match('/[a-zA-Z0-9_]+/', $username)) {
						// Does email contain valid characters? Is it a real email?
						if(filter_var($email, FILTER_VALIDATE_EMAIL)) {

							DB::_query('INSERT INTO users (username, password, email) VALUES (:username, :password, :email)', [
								'username'  => $username, 
								'password'  => password_hash($password, PASSWORD_BCRYPT),
								'email'		=> $email
							]);

							header('Location: signin.php');

						} else echo "<p class='error-msg'>Invalid email format.</p>";
					
					} else echo "<p class='error-msg'>Invalid username characters, allowed ones: [a-zA-Z0-9_]</p>";
				
				} else echo "<p class='error-msg'>Invalid password length, make sure to insert a password with length between 3 and 30 characters.</p>";
			
			} else echo "<p class='error-msg'>Invalid username length, make sure to insert a username with length between 3 and 30 characters.</p>";
		
		} else echo "<p class='error-msg'>Email already in use... <a href='singin.php'>Do you already have an account?</a></p>";
		
	} else echo "<p class='error-msg'>Username already taken.</p>";
	
} 

// If user is not logged in, display our registration form, otherwise... redirect him/her to the dashboard.
if(!Login::isLogged()) {

?>

<div class="container">
	<div class="col-xs-12">
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" class="signup form-group">
			<h1 class="form-msg">Sign Up</h1>

			<div class="form-control">
				<label for="username" class="sr-only">Username</label>
				<input type="text" class="input-phchat" name="username" placeholder="Username" />
			</div>
			<div class="form-control">
				<label for="Email" class="sr-only">Email</label>
				<input type="email" class="input-phchat" name="email" placeholder="Email" />
			</div>
			<div class="form-control">
				<input type="password" class="input-phchat" name="password" placeholder="Password" />
			</div>
			<button type="submit" name="signup" class="btn btn-phchat btn-primary">Sign Up</button>
		</form>

		<div class="already-have-account">
			<a href="signin.php">Already have an account?</a>
		</div>
	</div>
</div>

<?php } else header('Location: conversations.php'); ?>

<?php require('templates/footer.php'); ?>