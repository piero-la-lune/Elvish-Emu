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

<article class="article-all">

<h1>'.$album['name'].'</h1>

<p>'.\Michelf\Markdown::defaultTransform($album['comment']).'</p>
<p>&nbsp;</p>

<div class="div-imgs">

		';

		$videos = '';
		foreach ($album['files'] as $k => $f) {
			if ($f['type'] == 'image') {
				if ($f['thumbnail']) {
					$content .= ''
.'<a href="'.Url::parse('albums/'.$id.'/'.$k.'/view').'">'
	.'<img src="'.Url::parse('albums/'.$id.'/th/'.$k).'" />'
.'</a>';
				}
				else {
					$content .= ''
.'<a href="'.Url::parse('albums/'.$id.'/'.$k.'/view').'">'
	.'<img src="'.Url::parse('albums/'.$id.'/dl/'.$k).'" />'
.'</a>';
				}
			}
			elseif ($f['type'] == 'video') {
				if ($f['thumbnail']) {
					$videos .= ''
.'<a href="'.Url::parse('albums/'.$id.'/dl/'.$k).'">'
	.'<img src="'.Url::parse('albums/'.$id.'/th/'.$k).'" />'
.'</a>';
				}
				else {
					$videos .= ''
.'<a href="'.Url::parse('albums/'.$id.'/dl/'.$k).'">'
	.$f['filename']
.'</a>';
				}
			}
		}

		$content .= '

</div>
<div class="div-videos">'.$videos.'</div>

</article>

';

	}
	else {

		$load = 'error/404';

	}



?>