<?php

if (isset($_POST['action']) && isset($_POST['page'])) {

	$manager = Manager::getInstance();

	if (isset($_POST['action']) && $_POST['action'] == 'load'
		&& isset($_POST['album']) && isset($_POST['filename'])
	) {

		$album = isset($_POST['album']) ? $manager->getAlbum($_POST['album']) : false;

		if ($album !== false
			&& isset($album['files'][$_POST['filename']])
			&& file_exists($album['files'][$_POST['filename']]['path'])
			&& $album['files'][$_POST['filename']]['type'] == 'image'
		) {

			die(json_encode(array(
				'status' => 'success',
				'html' => Manager::printImage($_POST['album'], $album, $_POST['filename']),
				'url' => Url::parse('albums/'.$_POST['album'].'/'.$_POST['filename'].'/edit')
			)));

		}
		else {

			die(json_encode(array('status' => 'error')));

		}

	}

}

die(json_encode(array('status' => 'error')));

?>