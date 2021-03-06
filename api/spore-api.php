<?php

//  PHP Library for accessing the Spore API
//  Version 0.9
//  Created on 2/12/09  by Michael Twardos


/*  This php library provides functions for accessing the Spore API.
Simply include this library in an application to use the functions provided.
To view how functions work, un-comment each Tester example to understand the format in which the data is returned
*/

include('utf.php');

/**
* Fetch XML from URL with fopen. Requires `allow_url_fopen` to be set to TRUE
*
* @param string $url URL to fetch
* @return SimpleXMLElement Resulting XML
*/
function getRestService($url) {

	if (function_exists('curl_init')) {

		return getRestServiceCurl($url);

	} elseif (ini_get('allow_url_fopen') == true) {

		$file = fopen($url, 'r');
		if ($file === false) {
			echo 'Cannot open asset url';
			return '0';

		} else {
			$urldata = stream_get_contents($file);
			fclose($file);

			// Convert to UTF-8 for SimpleXML to work
			$urldata = mb_convert_encoding($urldata, 'UTF-8');

			try {
				$xml = new SimpleXMLElement($urldata);
				$result = $xml;
			} catch (Exception $e) {
				echo 'Bad XML';
				$result = '0';
			}
			return $result;
		}

	} else {
		echo 'No ways to fetch XML, sorry';
	}
}

/**
* Fetch XML from URL with curl module. Curl supports gzip encoding, so we can fetch XML faster
*
* @param string $url URL to fetch
* @return SimpleXMLElement Resulting XML
*/
function getRestServiceCurl($url) {

	$ch = curl_init($url);
	// set url
	// This was already set on curl_init (hopefully)
	//	curl_setopt($ch, CURLOPT_URL, "maxis.com");

	//return the transfer as a string
	// We'll use deflate for now until I figure out what's wrong with 'gzip' option
	// Unfortunately i have no curl to test it right now, maybe installing it later this week
	curl_setopt($ch, CURLOPT_ENCODING, 'deflate');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// $output contains the output string
	$data = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);

	if ($data === false) {
		echo 'Cannot open asset url';
		return '0';

	} else {
		// Convert to UTF-8 for SimpleXML to work
		$data = mb_convert_encoding($data, 'UTF-8');

		try {
			$xml = new SimpleXMLElement($data);
			$result = $xml;

		} catch (Exception $e) {

			echo 'Bad XML:' . $data;
			$result = '0';

		}

		return $result;
	}
}



function getStats() {
	$statsservice = 'http://www.spore.com/rest/stats';
	$statsxml = getRestService($statsservice);
	if ($statsxml == '0') {
		return $statsxml;
	} else {
		$statsinfo=array("totalUploads"=>$statsxml->totalUploads,
				 "dayUploads" => $statsxml->dayUploads,
				 "totalUsers" => $statsxml -> totalUsers,
				 "dayUsers" => $statsxml -> dayUsers);
		return $statsinfo;
	}

}

function getUserIdFromName($user) {
	$userservice = 'http://www.spore.com/rest/user/'.$user;
	$userxml = getRestService($userservice);
	if($userxml == '0') {
		return $userxml;
	} else {
		$userinfo = $userxml->id;
		return $userinfo;
	}

}

function getUserInfo($user){
	$userservice = 'http://www.spore.com/rest/user/'.$user;
	$userxml = getRestService($userservice);
	if($userxml == '0') {
		return $userxml;
	} else {
		$userinfo = array(
			"name"=>$userxml->name ,
			"id" => $userxml->id ,
			"image" => $userxml->image,
			"tagline" => $userxml->tagline,
			"creation" => $userxml->creation
		);

		return $userinfo;
	}
}

function getBuddiesPerUser($user, $start, $length){
	$buddyservice= 'http://www.spore.com/rest/users/buddies/'.$user.'/'.$start.'/'.$length;
	$buddyxml = getRestService($buddyservice);
	if($buddyxml == '0') {
		return $buddyxml;
	} else {

		$buddies=array();

		foreach ($buddyxml->buddy as $buddy) {
			$buddyinfo = array(
				"name" => $buddy->name,
				"id" => $buddy->id
			);
			array_push($buddies, $buddyinfo);

		}
		return $buddies;
	}
}


function getAssetsPerUser($user, $start, $length, $type) {
	$assetservice= 'http://www.spore.com/rest/assets/user/'.$user.'/'.$start.'/'.$length.'/'.$type;
	$assetxml = getRestService($assetservice);
	if($assetxml == '0') {
		return $assetxml;
	} else {
		$assets = array();
		foreach($assetxml->asset as $asset) {
			$id = $asset->id;
			$image = 'http://www.spore.com/static/image/'.substr($id, 0, 3).'/'.substr($id, 3, 3).'/'.substr($id,6,3).'/'.$id.'_lrg.png';
			$thumb = 'http://www.spore.com/static/thumb/'.substr($id, 0, 3).'/'.substr($id, 3, 3).'/'.substr($id,6,3).'/'.$id.'.png';

			$assetinfo = array(
				"name" => $asset->name,
				"id" => $asset->id,
				"image" => $image,
				"thumb" => $thumb,
				"created" => $asset->created,
				"rating" => $asset->rating,
				"type" => $asset->type ,
				"subtype" => $asset->subtype,
				"parent" => $asset->parent
			);

			array_push($assets, $assetinfo);
		}
		return $assets;
	}
}

function getAchievementsPerUser($user, $start, $length){

	$achievementservice= 'http://www.spore.com/rest/achievements/'.$user.'/'.$start.'/'.$length;
	$achievementxml = getRestService($achievementservice);
	$achievements = array();

	foreach($achievementxml->achievement as $achievement) {
		$achievementinfo = array(
			"guid" => $achievement->guid,
			"date" => $achievement->date
		);
		array_push($achievements, $achievementinfo);
	}

	return $achievements;
}

function getSubscribedSporecastsPerUser($user){
	$sporecastservice= 'http://www.spore.com/rest/sporecasts/'.$user;
	$sporecastxml = getRestService($sporecastservice);
	$sporecasts = array();
	foreach($sporecastxml->sporecast as $sporecast) {
		$sporecastinfo = array(
			"title" => $sporecast->title,
			"id" => $sporecast->id,
			"author" => $sporecast->author,
			"updated" => $sporecast->updated,
			"rating" => $sporecast->rating,
			"subscriptioncount" => $sporecast->subscriptioncount,
			"tags" => $sporecast->tags,
			"assetcount" => $sporecast->count
		);
		array_push($sporecasts, $sporecastinfo);
	}

	return $sporecasts;
}

function getInfoPerAsset($assetid){
	$assetinfoservice= 'http://www.spore.com/rest/asset/'.$assetid;
	$assetxml = getRestService($assetinfoservice);
	if ($assetxml->status == 0) {
		echo 'Returned asset is inactive' . "<br>\n";
	}

	$asset = array(
		"name"=> $assetxml->name ,
		"author"=> $assetxml->author ,
		"authorid"=> $assetxml->authorid ,
		"created"=> $assetxml->created ,
		"description"=> $assetxml->description ,
		"tags"=> $assetxml->tags ,
		"type"=> $assetxml->type ,
		"subtype" => $assetxml->subtype,
		"sprint" => $assetxml->sprint,
		"rating"=> $assetxml->rating ,
		"parent"=> $assetxml->parent
	);

	return $asset;
}

function getCreatureInfo($creatureid){
	$assetinfoservice= 'http://www.spore.com/rest/creature/'.$creatureid;
	$assetxml = getRestService($assetinfoservice);

	if ($assetxml->status == 0) {
		echo 'Returned asset is inactive' . "<br>\n";
	}

	$asset = array(
		"cost" => $assetxml->cost ,
		"health" => $assetxml->health ,
		"height" => $assetxml->height ,
		"meanness" => $assetxml->meanness ,
		"cuteness" => $assetxml->cuteness ,
		"sense" => $assetxml->sense ,

		"bonecount" => $assetxml->bonecount ,
		"footcount" => $assetxml->footcount ,
		"graspercount" => $assetxml->graspercount ,
		"basegear" => $assetxml->basegear ,

		"carnivore" => $assetxml->carnivore ,
		"herbivore" => $assetxml->herbivore ,

		"glide" => $assetxml->glide ,
		"sprint" => $assetxml->sprint ,
		"stealth" => $assetxml->stealth ,

		"bite" => $assetxml->bite ,
		"charge" => $assetxml->charge ,
		"strike" => $assetxml->strike ,
		"spit" => $assetxml->spit ,

		"feet" => IntVal($assetxml->footcount),
		"hand" => IntVal($assetxml->graspercount),

		"sing" => $assetxml->sing ,
		"dance" => $assetxml->dance ,
		"gesture" => $assetxml->gesture ,
		"posture" => $assetxml->posture
	);

	return $asset;
}

function getCreaturePreview($creatureid){
	$assetinfoservice = 'http://www.spore.com/rest/creature/' . $creatureid;
	$assetxml = getRestService($assetinfoservice);

	if ($assetxml->status == 0) {
		return null; // asset inactive
	}

	$asset = array(
		"thumb"=> $assetxml->thumb ,
		"name"=> $assetxml->name
	);
	return $asset;
}

function getCreatureModel($creatureid){
	$assetinfoservice = 'http://static.spore.com/static/model/' . substr($creatureid, 0, 3) . '/' . substr($creatureid, 3, 3) . '/' . substr($creatureid, 6, 3) . '/' . $creatureid . '.xml';
	$assetxml = getRestService($assetinfoservice);

	$props = $assetxml->properties;
	$asset = array("skincolor1" => $props->skincolor1);
	return $asset;
}

function getCommentsPerAsset($assetid, $start, $length) {
	$assetcommentservice = 'http://www.spore.com/rest/comments/'.$assetid.'/'.$start.'/'.$length;
	echo $assetcommentservice;
	$commentsxml = getRestService($assetcommentservice);
	$comments = array();
	foreach($commentsxml->comment as $comment) {

		$acomment = array(
			"message"=> $comment->message ,
			"sender"=> $comment->sender ,
			"date"=> $comment->date
		);
		array_push($comments, $acomment);

	}
	return $comments;
}

function getAssetsPerSporecast($sporecastid, $start, $length) {
	$assetservice = 'http://www.spore.com/rest/assets/sporecast/' . $sporecastid . '/' . $start . '/' . $length;
	$assetxml = getRestService($assetservice);
	$assets = array();

	foreach($assetxml->asset as $asset) {

		$assetinfo = array(
			"name" => $asset->name,
			"id" => $asset->id
		);
		array_push($assets, $assetinfo);

	}

	return $assets;
}


function getAssetsFromQuery($query, $start, $length, $type){
	$assetservice = 'http://www.spore.com/rest/assets/search/'.urlencode($query).'/'.$start.'/'.$length.'/'.strtoupper($type);
	$assetxml = getRestService($assetservice);
	if ($assetxml == '0') {
		return '0';
	} else {
		$assets = array();
		foreach($assetxml->asset as $asset) {
			$id = $asset->id;
			$image = 'http://www.spore.com/static/image/' . substr($id, 0, 3) . '/' . substr($id, 3, 3) . '/' . substr($id,6,3) . '/' . $id . '_lrg.png';
			$thumb = 'http://www.spore.com/static/thumb/' . substr($id, 0, 3) . '/' . substr($id, 3, 3) . '/' . substr($id,6,3) . '/' . $id . '.png';

			$assetinfo = array(
				"name" => $asset->name,
				"id" => $asset->id,
				"created" => $asset->created,
				"author" => $asset->author,
				"image"=> $image,
				"thumb"=> $thumb,
				"rating" => $asset->rating,
				"type" => $asset->type ,
				"subtype" => $asset->subtype,
				"parent" => $asset->parent
			);

			array_push($assets, $assetinfo);
		}
		return $assets;
	}
}