<?php

//  PHP Library for accessing the Spore API
//  Version 0.9
//  Created on 2/12/09  by Michael Twardos


/*  This php library provides functions for accessing the Spore API.
Simply include this library in an application to use the functions provided.
To view how functions work, un-comment each Tester example to understand the format in which the data is returned
*/

include('utf.php');

function getRestService($url){

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

/**  User Info Tester **/
/*
$user = getUserIdFromName('MaxisMichael');
echo $user;
*/

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

/**  User Info Tester **/
/*
$user = getUserInfo('MaxisMichael');
if($user == '0')
{
	echo 'No user';
}else{
	echo $user["id"];
	echo '<img src ="'.$user["image"].'" width=100>';
}
*/

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


/** Buddies Tester **/
/*
$bud = getBuddiesPerUser('MaxisMichael', '0', '4');
if($bud == '0')
{
	echo 'No user found.';
}else{
	for($i = 0; $i < sizeof($bud); $i++)
	{
		echo $bud[$i]["name"];
		echo '-'.$bud[$i]["id"].'<br>';
	}
}
*/
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

/** Users Assets Tester **/
/*
$ast = getAssetsPerUser('MaxisMichael', '0', '15', 'BLAH');
if($ast =='0')
{
	echo 'No user found.';
}else{
	for($i = 0; $i < sizeof($ast); $i++)
	{
		echo $ast[$i]["name"];
		echo '-'.$ast[$i]["image"].'<br>';
	}
}
*/

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

/** Users Achievements Tester **/
/*
$ach = getAchievementsPerUser('MaxisCactus', '0', '15');
for($i = 0; $i < sizeof($ach); $i++)
{
	echo $ach[$i]["guid"];
	echo '-'.$ach[$i]["date"].'<br>';
}
*/

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

/**   User's Sporecasts Tester **/
/*$sporecasts = getSubscribedSporecastsPerUser('MaxisMichael');
for($i = 0; $i<sizeof($sporecasts); $i++)
{
	echo $sporecasts[$i]["title"].' - '.$sporecasts[$i]["author"];
}
*/


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

/** Info per Asset Tester**/
/*
$asset = getInfoPerAsset('500138306571');
echo '<br>'.$asset["name"];
echo '<br>'.$asset["type"];
echo '<br>'.$asset["rating"];
*/

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

/**  Comments Per Asset Tester **/
/*
$com=getCommentsPerAsset('500269321470', '0', '5');
for($i =0; $i<sizeof($com); $i++)
{
	echo $com[$i]["message"].' - '.$com[$i]["sender"].'<br>';
}
*/

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


/** Assets for a Sporecast  **/
/*
$ast = getAssetsPerSporecast('500190457259', '0', '15');
for($i = 0; $i < sizeof($ast); $i++)
{
	echo $ast[$i]["name"];
	echo '-'.$ast[$i]["id"].'<br>';
}
*/


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

/** Search Assets Tester **/
/*
$ast = getAssetsFromQuery('TOP_RATED', '0', '15', 'BLAH');
for($i = 0; $i < sizeof($ast); $i++)
{
	echo $ast[$i]["name"];
	echo '-'.$ast[$i]["rating"].'<br>';
}
*/


?>