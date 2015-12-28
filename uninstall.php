<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}
var_dump(dns_get_record('opensearch-cn-hangzhou.aliyuncs.com', 'A'));