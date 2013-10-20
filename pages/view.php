<?php

	$manager = Manager::getInstance();

	$album = isset($_GET['id']) ? $manager->getAlbum($_GET['id']) : false;

	if ($album !== false) {

		$id = $_GET['id'];

		$title = $album['name'];
		$content = '';

		if (canAccess('edit')) {

			$content .= '

<div class="div-actions-top">
	<a href="'.Url::parse('albums/'.$id.'/edit').'">
		'.mb_strtolower(Trad::V_EDIT).'
	</a>
</div>

			';
		}

		$content .= '

<h1>'.$album['name'].'</h1>

<p>'.\Michelf\Markdown::defaultTransform($album['comment']).'</p>

		';

	}
	else {

		$load = 'error/404';

	}



?>