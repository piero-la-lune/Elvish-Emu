<?php

	$language = $config['language'];
	if (isset($_POST['action']) && $_POST['action'] == 'edit') {
		$settings = new Settings();
		$ans = $settings->changes($_POST);
		if (!empty($ans)) {
			foreach ($ans as $v) {
				$this->addAlert(Trad::$settings[$v]);
			}
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_SETTINGS, 'alert-success');
			if ($config['language'] != $language) {
				$_SESSION['alert'] = array(
					'text' => Trad::A_SUCCESS_SETTINGS,
					'type' => 'alert-success'
				);
				header('Location: '.Url::parse('settings'));
				exit;
			}
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == 'add_user') {
		$settings = new Settings();
		$ans = $settings->changes_user_add($_POST);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_USER_ADD, 'alert-success');
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == 'rm_user') {
		$settings = new Settings();
		$ans = $settings->changes_user_rm($_POST);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_USER_RM, 'alert-success');
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == 'edit_user') {
		$settings = new Settings();
		$ans = $settings->changes_user_edit($_POST);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_USER_EDIT, 'alert-success');
		}
	}

	$title = Trad::T_SETTINGS;

	$languages = array();
	foreach (explode(',', LANGUAGES) as $v) {
		$languages[$v] = $v;
	}

	$users = '';
	foreach ($config['users'] as $k => $user) {
		$users .= '
<h3>'.Text::chars($user['login']).'</h3>
<form action="'.Url::parse('settings').'" method="post">
	<label for="password_'.$k.'">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password_'.$k.'" />
	<p class="p-tip">'.Trad::F_TIP_PASSWORD.'</p>

	<label for="rank_'.$k.'">'.Trad::F_RANK.'</label>
	<select name="rank" id="rank_'.$k.'">
		'.Text::options(Trad::$ranks, $user['rank']).'
	</select>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="edit_user" />
	<input type="hidden" name="lid" value="'.$k.'" />
</form>
<form action="'.Url::parse('settings').'" method="post">
	<p class="p-submit"><input type="submit" value="'.Trad::V_REMOVE.'" /></p>
	<input type="hidden" name="action" value="rm_user" />
	<input type="hidden" name="lid" value="'.$k.'" />
</form>
		';
	}

	$content = '

<h2>'.Trad::T_GLOBAL_SETTINGS.'</h2>

<form action="'.Url::parse('settings').'" method="post">

	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'
		.Text::chars($config['url']).'" />
	<label for="url_rewriting">'.Trad::F_URL_REWRITING.'</label>
	<input type="text" name="url_rewriting" id="url_rewriting" value="'
		.(($config['url_rewriting']) ? $config['url_rewriting'] : '').'" />
	<p class="p-tip">'.Trad::F_TIP_URL_REWRITING.'</p>
	<label for="language">'.Trad::F_LANGUAGE.'</label>
	<select id="language" name="language">
		'.Text::options($languages, $config['language']).'
	</select>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="edit" />
</form>

<p>&nbsp;</p>
<h2>'.Trad::T_USERS_SETTINGS.'</h2>

'.$users.'

<h3>'.Trad::F_ADD.'</h3>
<form action="'.Url::parse('settings').'" method="post">
	<label for="login">'.Trad::F_USERNAME.'</label>
	<input type="text" name="login" id="login" />

	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" />

	<label for="rank">'.Trad::F_RANK.'</label>
	<select name="rank" id="rank">
		'.Text::options(Trad::$ranks, array()).'
	</select>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="add_user" />
</form>

	';


?>