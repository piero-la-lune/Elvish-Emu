<?php

	$name = (isset($_POST['name'])) ? Text::chars($_POST['name']) : '';
	$comment = (isset($_POST['comment'])) ? Text::chars($_POST['comment']) : '';
	$logins = array();
	foreach ($config['users'] as $lid => $user) {
		$logins[$lid] = $user['login'];
	}
	$access = (isset($_POST['access'])) ? $_POST['access'] : array(getLID());
	$dir = (isset($_POST['dir'])) ? $_POST['dir'] : array();

	$manager = Manager::getInstance();

	if (isset($_POST['action']) && $_POST['action'] == 'new') {
		$ans = $manager->newAlbum($_POST);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$_SESSION['alert'] = array(
				'text' => Trad::A_SUCCESS_NEW_ALBUM,
				'type' => 'alert-success'
			);
			header('Location: '.Url::parse('albums/'.$manager->getLastInserted()));
			exit;
		}
	}

	$title = Trad::T_NEW_ALBUM;

	$content = '

<h1>'.Trad::T_NEW_ALBUM.'</h1>

<form action="'.Url::parse('new').'" method="post">

	<label for="name">'.Trad::F_NAME.'</label>
	<input type="text" id="name" name="name" value="'.$name.'" />

	<label for="comment">'.Trad::F_COMMENT.'</label>
	<textarea id="comment" name="comment">'.$comment.'</textarea>

	<label for="access">'.Trad::F_AUTHORIZED_USERS.'</label>
	<select id="access" name="access[]" multiple>
		'.Text::options($logins, $access).'
	</select>

	<label for="dir">'.Trad::F_DIR.'</label>
	<select id="dir" name="dir">
		'.Text::options($manager->getDirs(), $dir).'
	</select>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="new" />

</form>

	';



?>