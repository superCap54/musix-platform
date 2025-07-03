<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}


$music->site_title = 'Auto Post Video';
$music->site_description = $music->config->description;
$music->site_pagename = "Auto Post Video";
$music->site_content = loadPage("package/content", []);