<?php

# Elvish Emu
# Copyright (c) 2013-2014 Pierre Monchalin
# <http://bugs.derivoile.fr/Elvish-Emu/dashboard>
# 
# Permission is hereby granted, free of charge, to any person obtaining
# a copy of this software and associated documentation files (the
# "Software"), to deal in the Software without restriction, including
# without limitation the rights to use, copy, modify, merge, publish,
# distribute, sublicense, and/or sell copies of the Software, and to
# permit persons to whom the Software is furnished to do so, subject to
# the following conditions:
# 
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

define('NAME', 'Elvish Emu');
define('VERSION', '0.2');
define('AUTHOR', 'Pierre Monchalin');
define('URL', 'http://bugs.derivoile.fr/Elvish-Emu/dashboard');

### Languages
define('LANGUAGES', 'fr'); # Separated by a comma
define('DEFAULT_LANGUAGE', 'fr'); # Used only during installation

### Standart settings
define('SALT', 'How are you doing, pumpkin?');
define('TIMEOUT', 3600); # 1 hour
define('TIMEOUT_COOKIE', 3600*24*365); # 1 year

### Directories and files
define('DIR_CURRENT', dirname(__FILE__).'/');
define('DIR_DATABASE', dirname(__FILE__).'/database/');
define('DIR_LANGUAGES', dirname(__FILE__).'/languages/');
define('FOL_FILES', 'files/');
define('FOL_THUMBNAILS', 'thumbnails/');
define('FILE_CONFIG', 'config.php');
define('FILE_ALBUMS', 'albums.php');
define('FILE_TAGS', 'tags.php');

### Ranks
define('RANK_ADMIN', 'admin');
define('RANK_VISITOR', 'visitor');
define('RANK_NONE', 'none');

### Thanks to Sebsauvage and Shaarli for the way I store data
define('PHPPREFIX', '<?php /* '); # Prefix to encapsulate data in php code.
define('PHPSUFFIX', ' */ ?>'); # Suffix to encapsulate data in php code.

### UTF-8
mb_internal_encoding('UTF-8');

### Load classes
function loadclass($class) {
	$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	require dirname(__FILE__).'/classes/'.$class.'.class.php';
}
spl_autoload_register('loadClass');

### Default settings
if (is_file(DIR_DATABASE.FILE_CONFIG)) {
	$config = Text::unhash(get_file(FILE_CONFIG));
	# We need $config to load the correct language
	require DIR_LANGUAGES.'Trad_'.$config['language'].'.class.php';
}
else {
	# We load language first because we need it in $config
	if (isset($_POST['language']) && Text::check_language($_POST['language'])) {
		# Needed at installation
		require DIR_LANGUAGES.'Trad_'.$_POST['language'].'.class.php';
	}
	else {
		require DIR_LANGUAGES.'Trad_'.DEFAULT_LANGUAGE.'.class.php';
	}
	$config = Settings::get_default_config(DEFAULT_LANGUAGE);
}

### Upgrade
if ($config['version'] != VERSION) {
	require DIR_CURRENT.'upgrade.php';
	exit;
}

### Manage sessions
$cookie = session_get_cookie_params();
	# Force cookie path (but do not change lifetime)
session_set_cookie_params($cookie['lifetime'], Text::dir($_SERVER["SCRIPT_NAME"]));
	# Use cookies to store session.
ini_set('session.use_cookies', 1);
	# Force cookies for session.
ini_set('session.use_only_cookies', 1);
	# Prevent php to use sessionID in URL if cookies are disabled.
ini_set('session.use_trans_sid', false);
session_name('Elvish-Emu');
session_start();

$page = new Page();

### Returns the IP address of the client
# (used to prevent session cookie hijacking)
function getIPs() {
    $ip = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	$ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    	$ip .= '_'.$_SERVER['HTTP_CLIENT_IP'];
    }
    return $ip;
}

### Authentification
$settings = new Settings();
function getLID() {
	global $loggedin;
	if (!$loggedin || !isset($_SESSION['lid'])) {
		return '';
	}
	return $_SESSION['lid'];
}
function getRank() {
	global $loggedin;
	if (!$loggedin || !isset($_SESSION['rank'])) {
		return RANK_NONE;
	}
	return $_SESSION['rank'];
}
function canAccess($page) {
	return !isset(Page::$restricted[$page])
		|| in_array(getRank(), Page::$restricted[$page]);
}
function logout($cookie = false) {
	if (isset($_SESSION['uid'])) {
		unset($_SESSION['uid']);
		unset($_SESSION['lid']);
		unset($_SESSION['rank']);
		unset($_SESSION['ip']);
		unset($_SESSION['expires_on']);
	}
	if ($cookie && isset($_COOKIE['uid'])) {
		setcookie('uid', NULL, time()-3600);
		unset($_COOKIE['uid']);
	}
	return true;
}
function check_user($login, $password) {
	global $config;
	foreach ($config['users'] as $lid => $user) {
		if ($user['login'] == $login
			&& $user['password'] == Text::getHash($password)
		) {
			return $lid;
		}
	}
	return false;
}
function login($post, $bypass = false) {
	global $config, $page, $settings;
	$wait = $config['wait'];
	if (isset($wait[getIPs()]) && $wait[getIPs()]['time'] > time()) {
		$page->addAlert(str_replace(
			array('%duration%', '%period%'),
			Text::timeDiff($wait[getIPs()]['time'], time()),
			Trad::A_ERROR_LOGIN_WAIT
		));
		return false;
	}
	if (!$bypass) {
		if (!isset($post['login']) || !isset($post['password'])) {
			return false;
		}
		$lid = check_user($post['login'], $post['password']);
		if ($lid === false) {
			$settings->login_failed();
			$page->addAlert(Trad::A_ERROR_LOGIN);
			return false;
		}
		$cookie = (isset($post['cookie']) && $post['cookie'] == 'true');
	}
	else {
		$lid = $post;
		$cookie = true;
	}
	$uid = Text::randomKey(40);
	$_SESSION['uid'] = $uid;
	$_SESSION['lid'] = $lid;
	$_SESSION['rank'] = $config['users'][$lid]['rank'];
	$_SESSION['ip'] = getIPs();
	$_SESSION['expires_on'] = time()+TIMEOUT;
		# 0 means "When browser closes"
	session_set_cookie_params(0, Text::dir($_SERVER["SCRIPT_NAME"]));
	session_regenerate_id(true);
	if ($cookie) {
		$settings->add_cookie($lid, $uid);
		setcookie(
			'uid',
			$uid,
			time()+TIMEOUT_COOKIE,
			Text::dir($_SERVER["SCRIPT_NAME"])
		);
	}
	return true;
}
if (isset($_POST['action']) && $_POST['action'] == 'login') {
	logout(true);
	login($_POST);
}
elseif (isset($_POST['action']) && $_POST['action'] == 'logout') {
	logout(true);
}
if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])
	|| $_SESSION['ip'] != getIPs()
	|| time() > $_SESSION['expires_on']
) {
	logout();
	if (isset($_COOKIE['uid'])) {
		$lid = $settings->check_cookie($_COOKIE['uid']);
		$loggedin = $lid !== false && login($lid, true);
	}
	else {
		$loggedin = false;
	}
}
else {
	$_SESSION['expires_on'] = time()+TIMEOUT;
	$loggedin = true;
}

### Manage directories and files
function update_file($filename, $content) {
	if (file_put_contents(DIR_DATABASE.$filename, $content, LOCK_EX) === false
		|| strcmp(file_get_contents(DIR_DATABASE.$filename), $content) != 0)
	{
		die('Enable to write file “'. DIR_DATABASE.$filename.'”');
	}
}
function get_file($filename) {
	$text = file_get_contents(DIR_DATABASE.$filename);
	if ($text === false) {
		die('Enable to read file “'. DIR_DATABASE.$filename.'”');
	}
	return $text;
}
function check_dir($dirname) {
	if (!is_dir(DIR_DATABASE.$dirname)
		&& (!mkdir(DIR_DATABASE.$dirname, 0705)
			|| !chmod(DIR_DATABASE.$dirname, 0705))
	) {
		die('Enable to create directory “'. DIR_DATABASE.$dirname.'”');
	}
}
function check_file($filename, $content = '') {
	if (!is_file(DIR_DATABASE.$filename)) {
		update_file($filename, $content);
	}
}
check_dir('');
check_dir(FOL_FILES);
check_file(FILE_ALBUMS, Text::hash(array()));
check_file(FILE_TAGS, Text::hash(array()));
check_file('.htaccess', "Allow from none\nDeny from all\n");
check_file(FOL_FILES.'.htaccess', "Options -Indexes\n\nAllow from all\nDeny from none\n");

### Cron jobs
if (isset($cron_job) && $cron_job == true) {
	$manager = Manager::getInstance();
	echo 'Cron jobs for Elvish Emu ('.date('r').')'."\n";
	echo '==========================================================='."\n\n";
	echo 'Done.'."\n";
	exit;
}

### Load page
if (!is_file(DIR_DATABASE.FILE_CONFIG)) {
	$page->load('install');
}
elseif (!$loggedin) {
	$page->load('login');
}
elseif (!isset($_GET['page'])) {
	$page->load('home');
}
else {
	$page->load($_GET['page']);
}

$pagename = $page->getPageName();

$menu = '';
if ($page->printHeader()) {
	$menu .= ''
		.'<a href="'.Url::parse('home').'"'
			.($pagename == 'home' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_HOME)
		.'</a>'
	;
	if (canAccess('new')) {
		$menu .= ''
			.'<a href="'.Url::parse('new').'"'
				.($pagename == 'new' ? 'class="selected"' : '').'>'
				.mb_strtolower(Trad::T_NEW)
			.'</a>'
		;
	}
	if (canAccess('settings')) {
		$menu .= ''
			.'<a href="'.Url::parse('settings').'"'
				.($pagename == 'settings' ? 'class="selected"' : '').'>'
				.mb_strtolower(Trad::T_SETTINGS)
			.'</a>'
		;
	}
	$menu .= ''
		.'<a href="#" id="logout">'
			.mb_strtolower(Trad::T_LOGOUT)
		.'</a>'
	;
}

?>

<!DOCTYPE html>

<html dir="ltr" lang="fr">

	<head>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

		<link rel="stylesheet" href="<?php echo Url::parse('public/css/app.min.css'); ?>" />

		<title><?php echo $page->getTitle(); ?></title>

	<body>

		<?php echo $page->getAlerts(); ?>

		<header>
			<nav>
				<?php echo $menu; ?>
			</nav>
		</header>

		<section class="inner">
			<?php echo $page->getContent(); ?>
		</section>

		<form id="form-logout" action="<?php echo Url::parse('home'); ?>" method="post">
			<input type="hidden" name="action" value="logout" />
		</form>

		<script><?php
echo "var ajax_url = '".Url::parse('ajax')."';";
echo "var m_error_ajax = '".Text::js_str(Trad::A_ERROR_AJAX)."';";
echo "var m_error_login = '".Text::js_str(Trad::A_ERROR_AJAX_LOGIN)."';";
echo "var page = '".Text::js_str($_GET['page'])."';";
		?></script>
		<script src="<?php echo Url::parse('public/js/app.min.js'); ?>"></script>

	</body>

</html>