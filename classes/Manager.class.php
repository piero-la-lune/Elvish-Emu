<?php

# Albums model :
# 	name => escaped (string)
# 	comment => (string)
# 	files => (File)
# 	access => (string array)
#
# Files model :
#	type => (string) 'image|video'
#	filename => escaped (string)
#	path => (string)
#	comment => (string)

class Manager {

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
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['name'])) {
			return Trad::A_ERROR_EMPTY_NAME;
		}
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
			'files' => array(),
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
			|| !isset($this->albums[$id])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['name'])) {
			return Trad::A_ERROR_EMPTY_NAME;
		}
		$access = array();
		foreach ($post['access'] as $lid) {
			if (isset($config['users'][$lid])) { $access[] = $lid; }
		}
		$this->albums[$id] = array(
			'name' => Text::chars($post['name']),
			'comment' => $post['comment'],
			'files' => array(),
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

	public function getLastInserted() {
		return $this->lastInserted;
	}

}

?>