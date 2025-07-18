<?php

if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$currentDays = date('t');

$dayStart = "";
$playsThisMonth = '';
$LikesThisMonth = '';
$DownloadsThisMonth = '';
$SalesThisMonth = '';
runPlugin("OnDashBoardPageLoad");
for ($i=1; $i <= $currentDays; $i++) {
	$f = sprintf("%02d", $i);
	$dayStart .= "'$f',";

	$thisMonthPlayCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_VIEWS, 'count(*)');
	$playsThisMonth .= "'$thisMonthPlayCount',";

	$thisMonthLikeCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_LIKES, 'count(*)');
	$LikesThisMonth .= "'$thisMonthLikeCount',";

	$thisMonthDownloadsCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_DOWNLOADS, 'count(*)');
	$DownloadsThisMonth .= "'$thisMonthDownloadsCount',";

	$thisMonthSalesCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_PURCHAES, 'count(*)');
	$SalesThisMonth .= "'$thisMonthSalesCount',";
}

$thisMonthDays =  "[" . $dayStart . "]";

$playsThisMonth =  "[" . $playsThisMonth . "]";
$LikesThisMonth =  "[" . $LikesThisMonth . "]";
$DownloadsThisMonth =  "[" . $DownloadsThisMonth . "]";
$SalesThisMonth =  "[" . $SalesThisMonth . "]";

$mostPlayedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostCommentedSongHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostLikedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostDownloadedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';

$mostPlayedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 7");

if (!empty($mostPlayedSongs)) {
	$mostPlayedSongsHTML = "";
	foreach ($mostPlayedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = number_format($userSong->views);
		$songData->songArray['key'] = $key + 1;
		$mostPlayedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostCommentedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_COMMENTS . ".id) AS " . T_COMMENTS . "
FROM " . T_SONGS . " LEFT JOIN " . T_COMMENTS . " ON " . T_SONGS . ".id = " . T_COMMENTS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_COMMENTS . " DESC LIMIT 7");

if (!empty($mostCommentedSongs)) {
	$mostCommentedSongHTML = "";
	foreach ($mostCommentedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = number_format($userSong->comments);
		$songData->songArray['key'] = $key + 1;
		$mostCommentedSongHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostLikedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_LIKES . ".id) AS " . T_LIKES . "
FROM " . T_SONGS . " LEFT JOIN " . T_LIKES . " ON " . T_SONGS . ".id = " . T_LIKES . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_LIKES . " DESC LIMIT 7");

if (!empty($mostLikedSongs)) {
	$mostLikedSongsHTML = "";
	foreach ($mostLikedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = number_format($userSong->likes);
		$songData->songArray['key'] = $key + 1;
		$mostLikedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostDownloadedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_DOWNLOADS . ".id) AS " . T_DOWNLOADS . "
FROM " . T_SONGS . " LEFT JOIN " . T_DOWNLOADS . " ON " . T_SONGS . ".id = " . T_DOWNLOADS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_DOWNLOADS . " DESC LIMIT 7");

if (!empty($mostDownloadedSongs)) {
	$mostDownloadedSongsHTML = "";
	foreach ($mostDownloadedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = number_format($userSong->downloads);
		$songData->songArray['key'] = $key + 1;
		$mostDownloadedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$recentSalesHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M5,6H23V18H5V6M14,9A3,3 0 0,1 17,12A3,3 0 0,1 14,15A3,3 0 0,1 11,12A3,3 0 0,1 14,9M9,8A2,2 0 0,1 7,10V14A2,2 0 0,1 9,16H19A2,2 0 0,1 21,14V10A2,2 0 0,1 19,8H9M1,10H3V20H19V22H1V10Z" /></svg>' . lang("No sales found") . '</div>';

$recentSales = $db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->get(T_PURCHAES, 50);
if (!empty($recentSales)) {
	$recentSalesHTML = "";
	foreach ($recentSales as $key => $userSale) {
		if (!empty($userSale->track_id)) {
			$songData = songData($userSale->track_id);
			$songData->songArray['count'] = '$' . number_format($userSale->final_price);
			$songData->songArray['key'] = '#' . $userSale->id;
			$songData->songArray['load'] = 'track/' . $songData->songArray['s_audio_id'];
			$songData->songArray['type'] = lang('Song');
			$recentSalesHTML .= loadPage("dashboard/list", $songData->songArray);
		}
		elseif (!empty($userSale->event_id)) {
			$event = GetEventById($userSale->event_id);
			$recentSalesHTML .= loadPage("dashboard/sell_list", array('count' => '$' . number_format($userSale->final_price),
		                                                         'key' => '#' . $userSale->id,
		                                                         's_name' => $event->name,
		                                                         'type' => lang('Ticket'),
		                                                         'load' => $event->data_load,
		                                                         's_url' => $event->url));
		}
	}
}


$music->site_title = lang('Dashboard');
$music->site_description = $music->config->description;
$music->site_pagename = "dashboard";


$TOTAL_PRODUCTS_PURCHAES = $db->where('order_hash_id IN (SELECT hash_id FROM ' . T_ORDERS . ' WHERE product_owner_id = ?)', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_SONGS_PURCHAES = $db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_TICKETS_PURCHAES = $db->where('event_id IN (SELECT id FROM ' . T_EVENTS . ' WHERE user_id = ?)', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');


$TOTAL_PRODUCTS_PURCHAES_TODAY = $db->where('order_hash_id IN (SELECT hash_id FROM ' . T_ORDERS . ' WHERE product_owner_id = ?) AND DATE(`timestamp`) = CURDATE()', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_SONGS_PURCHAES_TODAY = $db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?) AND DATE(`timestamp`) = CURDATE()', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_TICKETS_PURCHAES_TODAY = $db->where('event_id IN (SELECT id FROM ' . T_EVENTS . ' WHERE user_id = ?) AND DATE(`timestamp`) = CURDATE()', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');

$TOTAL_PRODUCTS_PURCHAES_MONTH = $db->where('order_hash_id IN (SELECT hash_id FROM ' . T_ORDERS . ' WHERE product_owner_id = ?) AND MONTH(`timestamp`) = MONTH(CURDATE())', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_SONGS_PURCHAES_MONTH = $db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?) AND MONTH(`timestamp`) = MONTH(CURDATE())', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');
$TOTAL_TICKETS_PURCHAES_MONTH = $db->where('event_id IN (SELECT id FROM ' . T_EVENTS . ' WHERE user_id = ?) AND MONTH(`timestamp`) = MONTH(CURDATE())', [$user->id])->getValue(T_PURCHAES, 'sum(final_price)');





$dashboardData = ['views'=>'1,254,879','earnings'=>'8,742.35','activeSong'=>'10','countriesReached'=>'42'];

$monthlyData = [
    ['month'=>'January','views'=>'88,543','earnings'=>'211114.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'214.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'2111114.56'],
    ['month'=>'January','views'=>'88,543','earnings'=>'211114.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'214.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'2111114.56'],
    ['month'=>'January','views'=>'88,543','earnings'=>'211114.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'214.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'2111114.56'],
    ['month'=>'January','views'=>'88,543','earnings'=>'211114.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'214.56'],
    ['month'=>'January','views'=>'21,543','earnings'=>'2111114.56'],
];

$songsDataList = [
    ['title' => 'Song 1','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United States','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 1','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United Kingdom','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 1','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'Germany','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 1','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United States','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 1','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United Kingdom','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 2','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'Germany','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 2','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United States','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 2','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'United Kingdom','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 2','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'Germany','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
    ['title' => 'Song 2','platform' => 'All Platform','icon'=>'','date'=>'May 15 - Jun 15, 2025','views'=>'384,729','topCountry'=>'Germany','earns'=>'2458.92','imgSrc'=>"https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish"],
];
$countryList = [
    ['countryName'=>'United States','value'=>'458325','color'=>'#ff7a00'],
    ['countryName'=>'United Kingdom','value'=>'287192','color'=>'#00b4ff'],
    ['countryName'=>'Germany','value'=>'198745','color'=>'#64ffda'],
    ['countryName'=>'Canada','value'=>'156387','color'=>'#ff00ff'],
    ['countryName'=>'Other','value'=>'156387','color'=>'#fff012'],
];
$songsList = [
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/2.wav','title' => 'Song 2','isrc'=>'QZES99032915','fileExtension'=>pathinfo("http://localhost/music/2.wav", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)],
    ['imgSrc' => 'https://readdy.ai/api/search-image?query=modern%20electronic%20music%20album%20cover%20with%20neon%20lights%20and%20geometric%20shapes%2C%20dark%20background%2C%20minimalist%20design%2C%20high%20quality%2C%20professional&width=400&height=400&seq=1&orientation=squarish', 'audioSrc' => 'http://localhost/music/1.mp3','title' => 'Song 1','isrc'=>'QZES72032915','fileExtension'=>pathinfo("http://localhost/music/1.mp3", PATHINFO_EXTENSION)]
];

$data = ['dashboardData'=>$dashboardData,'monthlyData'=>$monthlyData,'songsList'=>$songsList,'countryList'=>$countryList,'songsDataList'=>$songsDataList];
$music->site_content = loadPage("dashboard/content",$data);


?>