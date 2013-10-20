<?php

	$manager = Manager::getInstance();

	$album = isset($_GET['id']) ? $manager->getAlbum($_GET['id']) : false;

	if ($album !== false) {

		$id = $_GET['id'];

		$name = (isset($_POST['name']))?
			Text::chars($_POST['name']):
			$album['name'];
		$comment = (isset($_POST['comment']))?
			Text::chars($_POST['comment']):
			Text::chars($album['comment']);
		$logins = array();
		foreach ($config['users'] as $lid => $user) {
			$logins[$lid] = $user['login'];
		}
		$access = (isset($_POST['access']))?
			$_POST['access']:
			$album['access'];
		$dir = (isset($_POST['dir']))?
			$_POST['dir']:
			$album['dir'];

		if (isset($_POST['action']) && $_POST['action'] == 'edit') {
			$ans = $manager->editAlbum($id, $_POST);
			if ($ans !== true) {
				$this->addAlert($ans);
			}
			else {
				$_SESSION['alert'] = array(
					'text' => Trad::A_SUCCESS_EDIT_ALBUM,
					'type' => 'alert-success'
				);
				header('Location: '.Url::parse('albums/'.$id));
				exit;
			}
		}
		if (isset($_POST['action']) && $_POST['action'] == 'rm') {
			$ans = $manager->rmAlbum($id);
			if ($ans !== true) {
				$this->addAlert($ans);
			}
			else {
				$_SESSION['alert'] = array(
					'text' => Trad::A_SUCCESS_RM_ALBUM,
					'type' => 'alert-success'
				);
				header('Location: '.Url::parse('home'));
				exit;
			}
		}

		$title = '';
		$content = '

<form action="'.Url::parse('albums/'.$id.'/edit').'" method="post">

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
	<input type="hidden" name="action" value="edit" />

</form>

<form action="'.Url::parse('albums/'.$id.'/edit').'" method="post">
	<p class="p-submit"><input type="submit" value="'.Trad::V_REMOVE.'" /></p>
	<input type="hidden" name="action" value="rm" />
</form>



		';

	}
	else {

		$load = 'error/404';

	}

?>