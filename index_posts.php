<?php

/** WordPress Administration Bootstrap */
$admin_abspath = str_replace(site_url(), ABSPATH, admin_url());
//add_plugins_page()
require_once(dirname(__FILE__) . '/admin.php');