<?php

# Albums model :
# 	name => escaped (string)
# 	comment => (string)
# 	dir => (string)
# 	files => (File)
# 	access => (string array)
#
# Files model :
#	type => (string) 'image|video'
#	filename => escaped (string)
#	path => (string)
#	thumbnail => (string) | false
#	comment => (string)

class Manager {

	protected $img_extensions = array(
		'png', 'PNG',
		'jpg', 'jpeg', 'JPG', 'JPEG',
		'gif', 'GIF'
	);
	protected $vid_extensions = array(
		'avi', 'AVI',
		'mp4', 'MP4',
		'mov', 'MOV'
	);

	private static $instance;
	protected $albums = array();
	protected $lastInserted = null;

	public function __construct() {
		global $config;
		$this->albums = Text::unhash(get_file(FILE_ALBUMS));
	}

	public static function getInstance($project = NULL) {
		if (!isset(self::$instance)) {
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	protected function save() {
		update_file(FILE_ALBUMS, Text::hash($this->albums));
	}

	public function getAlbum($id) {
		if (isset($this->albums[$id])
			&& in_array(getLID(), $this->albums[$id]['access'])
		) {
			return $this->albums[$id];
		}
		return false;
	}

	public function getAlbums() {
		$albums = array();
		foreach ($this->albums as $k => $a) {
			if (in_array(getLID(), $a['access'])) { $albums[$k] = $a; }
		}
		return $albums;
	}

	public function newAlbum($post) {
		global $config;
		if (!isset($post['name']) || !isset($post['comment'])
			|| !isset($post['access']) || !is_array($post['access'])
			|| !isset($post['dir'])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['name'])) { return Trad::A_ERROR_EMPTY_NAME; }
		$files = $this->getFiles($post['dir']);
		if ($files === false) { return Trad::A_ERROR_FORM; }
		$access = array();
		foreach ($post['access'] as $lid) {
			if (isset($config['users'][$lid])) { $access[] = $lid; }
		}
		do {
			$id = Text::randomKey();
		} while(isset($this->albums[$id]));
		$this->albums[$id] = array(
			'name' => Text::chars($post['name']),
			'comment' => $post['comment'],
			'dir' => $post['dir'],
			'files' => $files,
			'access' => $access
		);
		$this->lastInserted = $id;
		$this->save();
		return true;
	}

	public function editAlbum($id, $post) {
		global $config;
		if (!isset($post['name']) || !isset($post['comment'])
			|| !isset($post['access']) || !is_array($post['access'])
			|| !isset($post['dir'])
			|| !isset($this->albums[$id])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['name'])) { return Trad::A_ERROR_EMPTY_NAME; }
		$files = $this->getFiles($post['dir']);
		if ($files === false) { return Trad::A_ERROR_FORM; }
		$access = array();
		foreach ($post['access'] as $lid) {
			if (isset($config['users'][$lid])) { $access[] = $lid; }
		}
		$this->albums[$id] = array(
			'name' => Text::chars($post['name']),
			'comment' => $post['comment'],
			'dir' => $post['dir'],
			'files' => $files,
			'access' => $access
		);
		$this->save();
		return true;
	}

	public function rmAlbum($id) {
		global $config;
		if (!isset($this->albums[$id])) {
			return Trad::A_ERROR_FORM;
		}
		unset($this->albums[$id]);
		$this->save();
		return true;
	}

	public function editFile($album, $filename, $post) {
		if (!isset($post['comment'])) {
			return Trad::A_ERROR_FORM;
		}
		$this->albums[$album]['files'][$filename]['comment'] = $post['comment'];
		$this->save();
		return true;
	}

	public function getDirs() {
		$dir = opendir(DIR_DATABASE.FOL_FILES);
		$dirs = array();
		while (($entry = readdir($dir)) !== false) {
			if (is_dir(DIR_DATABASE.FOL_FILES.$entry)
				&& preg_match('#^[a-zA-Z0-9-_]+$#', $entry)
			) {
				$dirs[$entry] = $entry;
			}
		}
		return $dirs;
	}

	public function getFiles($dirname) {
		if (!is_dir(DIR_DATABASE.FOL_FILES.$dirname)
			|| !preg_match('#^[a-zA-Z0-9-_]+$#', $dirname)
		) {
			return false;
		}
		$dir = opendir(DIR_DATABASE.FOL_FILES.$dirname);
		$files = array();
		while (($entry = readdir($dir)) !== false) {
			if (!is_dir($entry)) {
				if (preg_match(
					'#^[a-zA-Z0-9-_]+\.('
						.implode('|', $this->img_extensions)
					.')$#',
					$entry
				)) {
					$files[$entry] = array(
						'type' => 'image',
						'filename' => $entry,
						'path' => DIR_DATABASE.FOL_FILES.$dirname.'/'.$entry,
						'thumbnail' => $this->thumbnailImg($entry, $dirname),
						'comment' => ''
					);
				}
				if (preg_match(
					'#^[a-zA-Z0-9-_]+\.('
						.implode('|', $this->vid_extensions)
					.')$#',
					$entry
				)) {
					$files[$entry] = array(
						'type' => 'video',
						'filename' => $entry,
						'path' => DIR_DATABASE.FOL_FILES.$dirname.'/'.$entry,
						'thumbnail' => $this->thumbnailVid($entry, $dirname),
						'comment' => ''
					);
				}
			}
		}
		ksort($files);
		return $files;
	}

	protected function thumbnailImg($filename, $dirname) {
		$maxWidth = 200;
		$maxHeight = 200;
		$path = DIR_DATABASE.FOL_FILES.$dirname.'/'.$filename;
		$pathNew = DIR_DATABASE.FOL_FILES.$dirname.'/'.FOL_THUMBNAILS.$filename;
		if (file_exists($pathNew)) { return $pathNew; }
		$ext = Text::get_ext($filename);
		if ($ext == 'jpg' || $ext == 'jpeg') {
			$img = imagecreatefromjpeg($path);
		}
		elseif ($ext == 'gif') {
			$img = imagecreatefromgif($path);
		}
		elseif ($ext == 'png') {
			$img == imagecreatefrompng($path);
		}
		else {
			return false;
		}
		$width = imageSX($img);
		$height = imageSY($img);
		$ratio = $width/$height;
		if ($width > $maxWidth) {
			$width = $maxWidth;
			$height = $maxWidth/$ratio;
		}
		if ($height > $maxHeight) {
			$width = $maxHeight*$ratio;
			$height = $maxHeight;
		}
		$newImg = imagecreatetruecolor($width, $height);
		imagecopyresampled($newImg, $img, 0, 0, 0, 0,
			$width, $height, imageSX($img), imageSY($img));
		check_dir(FOL_FILES.$dirname.'/'.FOL_THUMBNAILS);
		if ($ext == 'jpg' || $ext == 'jpeg') {
			imagejpeg($newImg, $pathNew);
		}
		elseif ($ext == 'gif') {
			imagegif($newImg, $pathNew);
		}
		elseif ($ext == 'png') {
			imagepng($newImg, $pathNew);
		}
		return $pathNew;
	}

	protected function thumbnailVid($filename, $dirname) {
/*		$ffmpeg = dirname(__FILE__).'/../../ffmpeg-mutu';
		$second = 15;
		$video = DIR_DATABASE.FOL_FILES.$dirname.'/'.$filename;
		$image = DIR_DATABASE.FOL_FILES.$dirname.'/'.FOL_THUMBNAILS.$filename.'.jpg';
		$command = "$ffmpeg  -itsoffset -$second  -i $video -vcodec mjpeg -vframes 1 -an -f rawvideo -s 150×84 $image";
		echo exec($command);
		echo $ffmpeg;
		if (file_exists($image)) {
			return DIR_DATABASE.FOL_FILES.$dirname.'/'.FOL_THUMBNAILS.$filename;
		}*/
		$pathNew = DIR_DATABASE.FOL_FILES.$dirname.'/'.FOL_THUMBNAILS.$filename.'.png';
		if (file_exists($pathNew)) { return $pathNew; }
		return false;
	}

	public function getLastInserted() {
		return $this->lastInserted;
	}

	public static function printImage($albumID, $album, $filename) {
		list($prev, $next) = self::getPrevNext($album['files'], $filename);
		$prevA = '';
		if ($prev !== false) {
			$prevA = '<a href="#" onclick="load(\''.$albumID.'\', \''.$prev.'\');">«</a>';
		}
		$nextA = '';
		if ($next !== false) {
			$nextA = '<a href="#" onclick="load(\''.$albumID.'\', \''.$next.'\');">»</a>';
		}

		return '

<div class="div-table div-img-header">
	<div class="div-cell div-a">'.$prevA.'</div>
	<div class="div-cell div-h">
		<h1>'.$filename.'</h1>
	</div>
	<div class="div-cell div-a">'.$nextA.'</div>
</div>

<div class="div-img">
	<img class="img-display" src="'.Url::parse('albums/'.$albumID.'/dl/'.$filename).'" />
</div>

<p>'.\Michelf\Markdown::defaultTransform($album['files'][$filename]['comment']).'</p>

		';
	}

	public static function getPrevNext($files, $filename) {
		$prev = false;
		$next = false;
		$done = false;
		foreach ($files as $f) {
			if ($f['type'] != 'image') { continue; }
			if ($done) { $next = $f['filename']; break; }
			if ($f['filename'] == $filename) { $done = true; }
			else { $prev = $f['filename']; }
		}
		return array($prev, $next);
	}

}

?>