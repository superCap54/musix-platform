<?php
runPlugin("OnSettingsPage");
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}
if (empty($path['options'][1])) {
	header("Location: $site_url");
	exit();
}
$settings_page = 'general';
if (empty($path['options'][2])) {
	$settings_page = 'general';
} else {
	if (in_array($path['options'][2], ['general', 'profile', 'delete', 'password', 'blocked','interest','balance','withdrawals','notification', 'manage-sessions', 'two-factor', 'affiliates','my_info','invitation_links','wallet','addresses','points'])) {
		$settings_page = secure($path['options'][2]);
        if ($settings_page == 'affiliates' && $music->config->affiliate_system != '1') {
            $settings_page = 'general';
        }
	}
}

if( $settings_page == 'delete' ){
    if( $music->config->delete_account == 'off' ){
        header("Location: $site_url/feed");
        exit();
    }
}

if( $settings_page == 'balance' || $settings_page == 'withdrawals' ){
    if($music->config->affiliate_system != '1' && $music->config->point_system != 'on'){
        header("Location: $site_url/feed");
        exit();
    }
}


$username = secure($path['options'][1]);

$getIDfromUser = $db->where('username', $username)->getValue(T_USERS, 'id');
if (empty($getIDfromUser) || !isAdmin()) {
	$getIDfromUser = $user->id;
}

$userData = userData($getIDfromUser);

$userData->owner  = false;

if ($music->loggedin == true) {
    $userData->owner  = ($user->id == $userData->id) ? true : false;
}
$music->userData = $userData;
$countries = '';
foreach ($countries_name as $key => $value) {
    $selected = ($key == $userData->country_id) ? 'selected' : '';
    $countries .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
}

$blocked_list = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12,4A4,4 0 0,1 16,8C16,9.95 14.6,11.58 12.75,11.93L8.07,7.25C8.42,5.4 10.05,4 12,4M12.28,14L18.28,20L20,21.72L18.73,23L15.73,20H4V18C4,16.16 6.5,14.61 9.87,14.14L2.78,7.05L4.05,5.78L12.28,14M20,18V19.18L15.14,14.32C18,14.93 20,16.35 20,18Z" /></svg>' . lang("No blocked users found") . '</div>';

$getBlocked = $db->where('user_id', $userData->id)->get(T_BLOCKS);
if (!empty($getBlocked)) {
	$blocked_list = '';
	foreach ($getBlocked as $key => $buser) {
		$blocked_list .= loadPage('settings/blocked_list', [
			'USER_DATA' => userData($buser->blocked_id)
		]);
	}
}

$withdrawal_history = "";
if( $settings_page == 'withdrawals' ){
    $user_withdrawals  = $db->where('user_id',$userData->id)->get(T_WITHDRAWAL_REQUESTS);
    foreach ($user_withdrawals as $withdrawal) {
        $music->withdrawal_stat = $withdrawal->status;
        $withdrawal_history .= LoadPage("settings/withdrawals-list",array(
            'W_ID' => $withdrawal->id,
            'W_REQUESTED' => date('Y-F-d', $withdrawal->requested),
            'W_AMOUNT' => number_format($withdrawal->amount),
            'W_CURRENCY' => $withdrawal->currency,
        ));
    }
}

$music->points_count = 0;
if( $settings_page == 'points' ){
    $user_points  = $db->where('user_id',$userData->id)->where('is_add', 1)->get(T_POINT_SYSTEM);
    foreach ($user_points as $point) {
        $music->points_count += $point->reword;
    }
}
$music->points_count = number_format($music->points_count);



$manage_sessions = "";
if( $settings_page == 'manage-sessions' ){
    $user_sessions  = $db->arrayBuilder()->where('user_id',$userData->id)->orderBy('time', 'DESC')->get(T_APP_SESSIONS);
    foreach ($user_sessions as $session) {
        $session['browser'] = 'Unknown';
        $session['time'] = time_Elapsed_String($session['time']);
        $session['platform'] = ucfirst($session['platform']);
        $session['ip_address'] = '';
        if ($session['platform'] == 'web' || $session['platform'] == 'windows') {
            $session['platform'] = 'Unknown';
        }
        if ($session['platform'] == 'Phone') {
            $session['browser'] = 'Mobile';
        }
        if ($session['platform'] == 'Windows') {
            $session['browser'] = 'Desktop Application';
        }
        if (!empty($session['platform_details'])) {
            $uns = unserialize($session['platform_details']);
            $session['browser'] = $uns['name'];
            $session['platform'] = ucfirst($uns['platform']);
            $session['ip_address'] = $uns['ip_address'];
        }
        $manage_sessions .= LoadPage("settings/session",$session);
    }
}
if( $settings_page == 'invitation_links' ){
    $music->available_links = GetAvailableLinks($userData->id);
    if ($music->config->user_links_limit > 0) {
        $music->generated_links = $music->config->user_links_limit - $music->available_links;
    }
    else{
        $music->generated_links = GetGeneratedLinks($userData->id);
    }
    $music->used_links = GetUsedLinks($userData->id);
}
if ($settings_page == 'addresses' && $music->config->store_system == 'on') {
    $music->address_html = '';
    $addresses = $db->where('user_id',$userData->id)->get(T_ADDRESS);
    if (!empty($addresses)) {
        foreach ($addresses as $key => $music->address) {
            $music->address_html .= loadPage("settings/addresses_list",array('name' => $music->address->name,
                                                                             'phone' => $music->address->phone,
                                                                             'id' => $music->address->id,
                                                                             'city' => $music->address->city,
                                                                             'zip' => $music->address->zip,
                                                                             'address' => $music->address->address,
                                                                             'country' => $music->address->country));
        }
    }
}

$payment_currency = $music->config->currency;
$currency         = !empty($music->config->currency_symbol_array[$music->config->currency]) ? $music->config->currency_symbol_array[$music->config->currency] : '$';

$music->setting_fields = UserFieldsData($userData->id);

$music->settings_page = $settings_page;
$music->site_title = lang("Settings");
$music->site_description = $music->config->description;
$music->site_pagename = "settings";
$music->site_content = loadPage("settings/$settings_page", [
	'USER_DATA' => $userData,
	'COUNTRIES_LAYOUT' => $countries,
	'blocked_list' => $blocked_list,
	'WITHDRAWAL_HISTORY_LIST' => $withdrawal_history,
    'SESSION_LIST' => $manage_sessions,
		'CURRENCY' => $currency
]);
