<?php

header('Content-Type:application/json');
header('Content-Disposition:attachment; filename="index-template.json"');
$content = file_get_contents(dirname(__FILE__) . '/includes/index-template.json');
header('Content-Length: ' . strlen($content));
echo $content;