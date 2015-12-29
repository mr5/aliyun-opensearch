<?php
/*
Plugin Name: AliYun Open Search
Plugin URI: http://www.aliyun.com/product/opensearch/
Description: Aliyun Open Search is a hosting service for structured data searching. Supporting data structures, sorting and data processing freedom to customize. Aliyun Open Search provides a simple, low cost, stable and efficient search solution for your sites or applications.
Author: Aliyun
Version: dev
Author URI: http://www.aliyun.com/product/opensearch/
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found.');
    exit('404 Not Found.');
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/AliyunOpenSearch.php';
define('ALI_OPENSEARCH_PLUGIN_NAME', 'aliyun-open-search');
$aos_version_file = plugin_dir_path(__FILE__) . 'VERSION';
if (is_file($aos_version_file)) {
    define('ALI_OPENSEARCH_PLUGIN_VERSION', file_get_contents($aos_version_file));
} else {
    define('ALI_OPENSEARCH_PLUGIN_VERSION', 'dev');
}
$aliyun_opensearch = new AliyunOpenSearch(ALI_OPENSEARCH_PLUGIN_NAME, ALI_OPENSEARCH_PLUGIN_VERSION);
$aliyun_opensearch->initialize();
$frontend = new AliyunOpenSearchFrontend(
    $aliyun_opensearch->getPluginName(),
    $aliyun_opensearch->getVersion(),
    AliyunOpenSearchClient::autoload()
);
$admin = new AliyunOpenSearchAdmin(
    $aliyun_opensearch->getPluginName(),
    $aliyun_opensearch->getVersion(),
    AliyunOpenSearchClient::autoload()
);
$aliyun_opensearch->run($admin, $frontend);

