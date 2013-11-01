<?php

	function readfile_chunked($filename, $retbytes = true) { 
		$chunksize = 1*(1024*1024); # how many bytes per chunk
		$buffer = '';
		$cnt = 0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; # return num. bytes delivered like readfile() does.
		}
		return $status;
	}

	$manager = Manager::getInstance();

	$album = isset($_GET['album']) ? $manager->getAlbum($_GET['album']) : false;

	if ($album !== false
		&& isset($_GET['filename'])
		&& isset($album['files'][$_GET['filename']])
		&& file_exists($album['files'][$_GET['filename']]['path'])
	) {

		# On n'affiche pas le contenu, car pour les gros fichiers, le temps
		# limite d'exécution de PHP est atteint : il faut rediriger
		# directement vers le lien réel du fichier

		if (isset($_GET['thumbnail'])
			&& $album['files'][$_GET['filename']]['thumbnail'] !== false
			&& file_exists($album['files'][$_GET['filename']]['thumbnail'])
		) {

			header('Location: '.Url::parse('database/files/'.$album['dir'].'/thumbnails/'.$_GET['filename']));
			exit;

/*			$file = $album['files'][$_GET['filename']];
			header('Content-Type: '.Text::get_mime_type($file['filename']));
			header('Content-Length: '.filesize($file['thumbnail']));
			readfile_chunked($file['thumbnail']);
			exit;*/

		}

		header('Location: '.Url::parse('database/files/'.$album['dir'].'/'.$_GET['filename']));
		exit;

/*		$file = $album['files'][$_GET['filename']];
		$size = trim(shell_exec('stat -c%s '.$file['path']));
		header('Content-Type: '.Text::get_mime_type($file['filename']));
		header('Content-Length: '.$size);
		readfile_chunked($file['path']);
		exit;*/

	}
	else {

		$load = 'error/404';

	}


?>