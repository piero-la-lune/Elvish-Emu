<?php

class Page {

	protected $page;
	protected $title;
	protected $content;
	protected $errors = array();

	protected $pages = array(
		'home',
		'install',
		'login',
		'error/404',
		'error/403',
		'settings',
		'new',
		'view',
		'edit'
	);

	public static $restricted = array(
		'settings' => array(RANK_ADMIN),
		'edit' => array(RANK_ADMIN)
	);

	public function load($page) {
		global $config;
		$this->page = $page;
		$path = dirname(__FILE__).'/../pages/'.$page.'.php';
		if (!in_array($page, $this->pages)) {
			$this->page = 'error/404';
			$path = dirname(__FILE__).'/../pages/error/404.php';
		}
		if (isset(self::$restricted[$this->page])
			&& !in_array(getRank(), self::$restricted[$this->page])
		) {
			$this->page = 'error/403';
			$path = dirname(__FILE__).'/../pages/error/403.php';
		}
		include($path);
		if (isset($load)) { $this->load($load); }
		else {
			$this->title = $title;
			$this->content = $content;
			$this->print_header = (isset($print_header)) ? $print_header : true;
		}
	}

	public function getPageName() {
		return $this->page;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getContent() {
		return $this->content;
	}

	public function printHeader() {
		return $this->print_header;
	}

	public function addAlert($txt, $type = 'alert-error') {
		$this->errors[] = array('text' => $txt, 'type' => $type);
	}
	public function getAlerts() {
		$txt = '';
		if (isset($_SESSION['alert'])) {
			$this->errors[] = $_SESSION['alert'];
			unset($_SESSION['alert']);
		}
		foreach ($this->errors as $error) {
			$txt .= '<div class="alert '.$error['type'].'" '
				.'onclick="this.style.display = \'none\';">'
				.$error['text']
			.'</div>';
		}
		return $txt;
	}

}

?>