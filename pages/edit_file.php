<?php

	$manager = Manager::getInstance();

	$album = isset($_GET['album']) ? $manager->getAlbum($_GET['album']) : false;

	if ($album !== false
		&& isset($_GET['filename'])
		&& isset($album['files'][$_GET['filename']])
		&& file_exists($album['files'][$_GET['filename']]['path'])
	) {

		$file = $album['files'][$_GET['filename']];

		$title = $album['name'].' » '.$file['filename'];

		$comment = isset($_POST['comment']) ? $_POST['comment'] : $file['comment'];
		if (isset($_POST['action']) && $_POST['action'] == 'edit') {
			$ans = $manager->editFile($_GET['album'], $_GET['filename'], $_POST);
			if ($ans !== true) {
				$this->addAlert($ans);
			}
			else {
				$_SESSION['alert'] = array(
					'text' => Trad::A_SUCCESS_EDIT_FILE,
					'type' => 'alert-success'
				);
				header('Location: '.Url::parse('albums/'.$_GET['album'].'/'.$_GET['filename'].'/view'));
				exit;
			}
		}

		$content = '

<h1>'.$file['filename'].'</h1>

<p><img class="img-small" src="'.Url::parse('albums/'.$_GET['album'].'/dl/'.$_GET['filename']).'" /></p>

<form action="'.Url::parse('albums/'.$_GET['album'].'/'.$_GET['filename'].'/edit').'" method="post">

	<label for="comment">'.Trad::F_COMMENT.'</label>
	<textarea name="comment" id="comment">'.Text::chars($comment).'</textarea>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="edit" />

</form>

		';

	}
	else {

		$load = 'error/404';

	}


?>