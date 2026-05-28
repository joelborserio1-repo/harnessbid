<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//INCLUDES
include dirname(__FILE__)."/../loader.php";
$class_user->authorised->id = 5;
//error_reporting(E_ALL);ini_set('display_errors', 1);

$jobs = Job::all();
foreach($jobs as $job) {
	$fail = true;

	switch ($job->type) {
		case 'listing_parents':

			$product = Products::find($job->object_id);
			if($product) {
				$result = $product->getFamilyData();
				if($result) {
					$fail = false;
				}
			}

			break;
	}

	if($fail) {
		$job->attempts++;
		$job->save();
	}

	if(!$fail || $job->attempts >= 5) {
		$job->delete();
	}

}

echo 'eof';
exit;
