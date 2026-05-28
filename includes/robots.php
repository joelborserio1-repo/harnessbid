<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

include "loader.php";

$lines = [
    'User-Agent: *',
    'Disallow: '.FE_rel.DIR_checkout,
    'Disallow: '.FE_rel.DIR_account,
    '',
    'Sitemap: '.FE_url.'sitemap.xml'
];

header('Content-type: text/plain');
echo implode(chr(13), $lines);
exit;

?>
