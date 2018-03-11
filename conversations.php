<?php

namespace Main;

use Classes\DB;
use Classes\Login;
use Classes\Image;

require_once('classes/DB.php');
require_once('classes/Login.php');
require_once('classes/Image.php');
require_once('templates/header.php');

$user_id = Login::isLogged();
$username = DB::_query('SELECT username FROM users WHERE id=:user_id', [ 'user_id' => $user_id ])[0]['username'];

// If the user wants to logout...
if(isset($_POST['logout'])) {
	// Delete the login data from the database.
	if(DB::_query('SELECT id FROM login_tokens WHERE user_id=:user_id', [ 'user_id' => $user_id ])) {
		DB::_query('DELETE FROM login_tokens WHERE user_id=:user_id', [ 'user_id' => $user_id ]);
	}

	// Delete the login cookies.
	if (isset($_COOKIE['SNID'])) {
	    unset($_COOKIE['SNID']);
	    setcookie('SNID', null, -1, '/');
	    header('Location: index.php');
	}
}

// If the user wants to change his profile pic...
if(isset($_FILES['avatar-file'])) {
	$username = DB::_query('SELECT username FROM users WHERE id=:user_id', [ 'user_id' => $user_id ])[0]['username'];

	if(!Image::$error) {
		$file = $_FILES['avatar-file'];
		$img_path = 'assets/avatars/profile-'.$user_id.'.jpg';
		Image::uploadImage($file, $img_path);

		if(DB::_query('SELECT img_path FROM imgs WHERE user_id=:user_id', [ 'user_id' => $user_id ])) {
			DB::_query('UPDATE imgs SET img_path=:img_path WHERE user_id=:user_id', [ 'img_path' => $img_path, 'user_id' => $user_id ]);
		} else {
			DB::_query('INSERT INTO imgs (img_path, user_id) VALUES (:img_path, :user_id)', [ 'img_path' => $img_path, 'user_id' => $user_id ]);
		}
	} else {
		echo "<h1>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iusto nulla voluptatem doloremque quibusdam ipsam adipisci provident rerum dicta animi! Libero cupiditate, maiores repudiandae veritatis quasi ducimus dolorem cumque, illo quaerat. ipsum dolor sit amet, consectetur adipisicing elit. Similique, id, ratione? In magni quidem non doloremque culpa aut perferendis impedit, at laborum possimus a ea autem eum ex ad alias. ipsum dolor sit amet, consectetur adipisicing elit. Magnam dolorum debitis provident, modi magni saepe velit eum aperiam sit a delectus nemo repellendus quo architecto, rerum iusto. Nesciunt, nemo, eum.</h1>";
	}
}

require_once('classes/Login.php');
require_once('templates/header.php');

if(Login::isLogged()) {

?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top header-top" style="background-color: #292b2c !important;">
  <a class="navbar-brand" href="conversations.php">PH Chat | <?php echo ucfirst($username) ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto"></ul>
    <div class="form-inline my-2 my-lg-0">
    	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="POST">
    		<button type="submit" name="logout" class="btn btn-phchat-logout btn-sm" style="margin-right: 40px;">Logout</button>
    	</form>
		<div class="account-box">
			<?php if(!Image::hasProfileImage($user_id)) { ?>
				<img src="assets/avatars/profile-default.png" alt="Profile Pic" class="rounded-img header-img" />
    		<?php } else { ?>
				<img src="assets/avatars/profile-<?php echo $user_id ?>.jpg" alt="<?php echo $username ?>'s avatar" class="rounded-img header-img" />
    		<?php } ?>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" id="js-profilePicUploadForm" method="POST" enctype="multipart/form-data">
    			<input type="file" name="avatar-file" class="profile-pic-upload" id="js-profilePicUpload" />
    		</form>
    	</div>
    </div>
  </div>
</nav>

<div class="container conversations" style="padding-top: 80px;">
	<div class="row">
		<!-- List of users who wrote you or you wrote them. -->
		<section class="col-md-4 conversations-section">
			<ul class="user-list">
				<?php
					// List of users who wrote you or you wrote them.
					if(DB::_query('SELECT users.username FROM users, messages WHERE messages.receiver = users.id OR messages.sender = users.id AND users.id = :user_id', [ 'user_id' => $user_id ])) {
						$usernames = DB::_query('SELECT * FROM messages, users WHERE (messages.receiver = :user_id OR messages.sender = :user_id) AND (messages.receiver = users.id OR messages.sender = users.id) GROUP BY users.id', [ 'user_id' => Login::isLogged() ]);

						foreach ($usernames as $single_username) {
							if($single_username['id'] != Login::isLogged()) {
								echo '<li class="user-who-wrote-you"><a href="#" data-id="'.$single_username['id'].'" class="user-list-item"></a>';
								if(!Image::hasProfileImage($single_username['id'])) {
									echo '<img src="assets/avatars/profile-default.png" alt="Profile Pic" />';
								} else {
									echo '<img src="assets/avatars/profile-'.$single_username['id'].'.jpg" alt="'.$username.'\'s avatar" class="rounded-img header-img" />';
								}
								echo '<span class="messager-name">'.$single_username['username'].'</span></li>';
							}
						}
					}
				?>
			</ul>
		
			<!-- Search users -->
			<div class="search-user" style="margin-top: 50px;">
				<input class="form-control mr-sm-2 ph-searchbar" id="js-searchUser" type="search" placeholder="Search" aria-label="Search">
				<div class="list-group list-results"></div>
		    </div>
		</section>

		<!-- Actual messages. -->
		<section class="col-sm-12 col-md-8 clearfix messages">
			<div class="messages-show" id="js-messagesContainer"></div>

			<div class="write-your-message">
				<form action="<?php htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" id="js-sendMessage">
					<input type="text" class="input-phchat" id="js-messageBody" name="message" placeholder="Write your message" style="display:none"/>
					<input type="submit" name="submit" style="display:none" />
				</form>
			</div>
		</section>
	</div>
</div>

<script>
	document.getElementById("js-profilePicUpload").onchange = function() {
    	document.getElementById("js-profilePicUploadForm").submit();
	};
</script>

<?php } else header('Location: index.php'); ?>

<?php require('templates/footer.php'); ?>