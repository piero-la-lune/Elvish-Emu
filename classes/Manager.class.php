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

	public function getFiles($dir) {
		if (!is_dir(DIR_DATABASE.FOL_FILES.$dir)
			|| !preg_match('#^[a-zA-Z0-9-_]+$#', $dir)
		) {
			return false;
		}
		$dir = opendir(DIR_DATABASE.FOL_FILES.$dir);
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
						'path' => DIR_DATABASE.FOL_FILES.$dir.$entry,
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
						'path' => DIR_DATABASE.FOL_FILES.$dir.$entry,
						'comment' => ''
					);
				}
			}
		}
		return $files;
	}

	public function getLastInserted() {
		return $this->lastInserted;
	}

}

?>