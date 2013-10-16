<?php

	header('HTTP/1.1 403 Forbidden');

	$title = Trad::T_403;

	$content = '

<h1>'.Trad::T_403.'</h1>

<p>'.Trad::S_FORBIDDEN.'</p>

	';

?>