<?php
//this file is in main folder and it works for me(yourWordpressWebsite.com/yourFile.php)

$path = $_SERVER['DOCUMENT_ROOT'];
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

global $wpdb;
$posts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='post'");

echo json_encode($posts);
exit();
?>   