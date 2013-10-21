<?php

	$manager = Manager::getInstance();

	$album = isset($_GET['album']) ? $manager->getAlbum($_GET['album']) : false;

	if ($album !== false
		&& isset($_GET['filename'])
		&& isset($album['files'][$_GET['filename']])
		&& file_exists($album['files'][$_GET['filename']]['path'])
		&& $album['files'][$_GET['filename']]['type'] == 'image'
	) {

		$file = $album['files'][$_GET['filename']];

		$title = $album['name'].' » '.$file['filename'];
		$print_header = false;
		$content = '

<article>
	'.Manager::printImage($_GET['album'], $album, $file['filename']).'
</article>

<div class="div-actions-top">

		';

		if (canAccess('edit_file')) {
			$content .= '
	<a href="'.Url::parse('albums/'.$_GET['album'].'/'.$file['filename'].'/edit').'">
		'.mb_strtolower(Trad::V_EDIT).'
	</a>
			';
		}

		$content .= '

	<a href="'.Url::parse('albums/'.$_GET['album']).'">
		'.mb_strtolower(Trad::W_BACK).'
	</a>

</div>

		';

	}
	else {

		$load = 'error/404';

	}


?>