<?php

	$manager = Manager::getInstance();

	$albums = $manager->getAlbums();

	$title = Trad::T_ALBUMS;

	$content = '

<h1>'.Trad::T_ALBUMS.'</h1>

';

	foreach ($albums as $id => $a) {
		$content .= '

<p>
<a class="a-album" href="'.Url::parse('albums/'.$id).'">'.$a['name'].'</a>
</p>

		';
	}

?>