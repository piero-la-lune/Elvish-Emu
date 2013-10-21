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

		$file = $album['files'][$_GET['filename']];
		header('Content-Type: '.Text::get_mime_type($file['filename']));
		header('Content-Length: '.filesize($file['path']));
		readfile_chunked($file['path']);
		exit;

	}
	else {

		$load = 'error/404';

	}


?>