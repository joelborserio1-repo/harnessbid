<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();

//INCLUDES
include("../loader.php");
$class_user->authorised->id = 5;

$curr_time = time();
$curr_time_round = floor($curr_time / (30 * 60)) * (30 * 60);

$time_end_24_min = strtotime('+24 hours', $curr_time_round);
$time_end_24_max = strtotime('+30 minutes', $time_end_24_min);
$time_end_1_min = strtotime('+1 hour', $curr_time_round);
$time_end_1_max = strtotime('+30 minutes', $time_end_1_min);

//-- 24 hour reminder
$listings = ProductListing::where([['time_close', '>=', $time_end_24_min], ['time_close', '<', $time_end_24_max]])->get();
if($listings->count() > 0) {
	foreach($listings as $listing) {
		if(!$listing->isLive()) {
			continue;
		}

		$product = $listing->product;
		if(!$product) {
			continue;
		}

		$watchlists = $product->watchlists;
		if($watchlists->count() > 0) {
			foreach($watchlists as $watchlist) {
				$watchlist->closingSoonEmail(24);
			}
		}
	}
}

//-- 1 hour reminder
$listings = ProductListing::where([['time_close', '>=', $time_end_1_min], ['time_close', '<', $time_end_1_max]])->get();
if($listings->count() > 0) {
	foreach($listings as $listing) {
		if(!$listing->isLive()) {
			continue;
		}

		$product = $listing->product;
		if(!$product) {
			continue;
		}

		$watchlists = $product->watchlists;
		if($watchlists->count() > 0) {
			foreach($watchlists as $watchlist) {
				$watchlist->closingSoonEmail(1);
			}
		}
	}
}

echo 'EOF';
exit;
