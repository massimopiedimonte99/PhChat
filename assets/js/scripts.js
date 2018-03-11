$(document).ready(function() {

	// List of inserted user in the sidebar view
	let insertedUserListItem = [];

	// List of already inserted user in the sidebar view
	let alreadyInsertedUserListItem = [];

	// If there is already a user added in the sidebar view, add it to alreadyInsertedUserListItem array.
	if($('.user-list').has('.user-who-wrote-you')) {
		$('.user-list').find('.user-who-wrote-you').each(function() {
			alreadyInsertedUserListItem.push('profile-' + $(this).find('a').attr('data-id'));
		});
	}

	// Send AJAX request to search a user without have to reload the page.
	$('#js-searchUser').on('input', (e) => {
		// Your input in the searchbar.
		let searchVal = $(e.target).val();

		$.ajax({
			type: 'POST',
			url: 'ajax.php',
			data: { searchVal },
			success: function(data) {
				$('.list-results').html(data);

				// If there is a user that has already been inserted in the sidebar view.
				$('.searched-user').each(function() {
					if($.inArray('profile-' + $(this).attr('data-id'), alreadyInsertedUserListItem) !== -1) {
						$(this).hide();
					}
				});

			}
		});
	});

	// Append a listed user in the related sidebar once you click on a search result.
	$(document).on('click', '.searched-user', (e) => {
		e.preventDefault();

		// The id of the searched user and his/her username.
		let user_id = $(e.target).attr('data-id');
		
		// Data passed from the PHP AJAX call that determines whether a user has a profile pic setted or not.
		let hasProfilePic = $(e.target).attr('data-image');

		let image = '<img src="assets/avatars/profile-default.png" alt="Profile Pic" />';

		// If the user has a profile pic, pick that one... otherwise, set the default one.
		if(hasProfilePic === 'yep') {
			image = '<img src="assets/avatars/profile-'+user_id+'.jpg" alt="Profile Pic" />';
		}

		// The actual username.
		let username = $(e.target).html();

		// If the item is already in the array, STOP... prevents duplicate listed user.
		if($.inArray('profile-' + user_id, insertedUserListItem) === -1 && $.inArray('profile-' + user_id, alreadyInsertedUserListItem) === -1) {
			// If it is not... just append the user to the related sidebar.
			$('.user-list').html($('.user-list').html() + `
				<li class="user-who-wrote-you">
					<a href="#" data-id="`+user_id+`" class='user-list-item'></a>`+
					image + `
					<span class="messager-name">`+username+`</span>
				</li>
			`);

			// Add the current user to the array... prevents duplicate listed user.
			insertedUserListItem.push('profile-' + user_id);
		}
	});

	// Interval in which the messages are dynamically loaded.
	var t;

	// Send AJAX request to display messages once you click on a listed user.
	$(document).on('click', '.user-list-item', (e) => {
		e.preventDefault();

		if(t) clearInterval(t);
		else console.log('nope');

		// The id of the clicked user.
		let receiver = $(e.target).attr('data-id');

		// Pass the id of the clicked user to the form, it will be the receiver of your messages.
		$('#js-sendMessage').attr('data-id', receiver);

		// When you click on a listed user, empty the textbox value.
		$('#js-messageBody').val('');

		// When you click on a listed user, display the textbox to chat him/her.
		$('#js-messageBody').show();


		t = setInterval(function() {
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: { receiver },
				success: function(data) {
					$('.messages-show').html(data);

					let messageContainer = document.getElementById('js-messagesContainer');
					messageContainer.scrollTop = messageContainer.scrollHeight;
				}
			});
		}, 100);
	});

	// Send AJAX request to send a message once you submit the related form.
	$('#js-sendMessage').on('submit', (e) =>  {
		e.preventDefault();

		// Your input in the textbox.
		let messageBody = $('#js-messageBody').val();
		
		// The receiver, hence the clicked user you are writing to.
		let user_id = $(e.target).attr('data-id');

		$.ajax({
			type: 'POST',
			url: 'ajax.php',
			data: { messageBody, user_id },
			success: function(data) {
				$('.messages-show').html($('.messages-show').html() + data);

				// Force scrollbar to the bottom.
				let messageContainer = document.getElementById('js-messagesContainer');
				messageContainer.scrollTop = messageContainer.scrollHeight;

				// Empty the textbox value.
				$('#js-messageBody').val('');
			}
		});
	});

});