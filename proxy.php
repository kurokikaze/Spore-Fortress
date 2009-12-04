<?php

include('api/spore-api.php');

$string = '';
$strings = array();

switch ($_GET['action']) {
	case 'user':
		$creatures = getAssetsPerUser($_GET['user'], 0, 5, 'creature');
		foreach ($creatures AS $creature) {
			$strings[] = ' "' . $creature['id'] . '" : { "name" : "' . $creature['name'] . '", "id" : "' . $creature['id'] . '", "thumb" : "' . $creature['thumb'] . '" } ';
		}
		break;

	case 'toprated':
		$creatures = getAssetsFromQuery('TOP_RATED_NEW', 0, 5, 'creature');
		foreach ($creatures AS $creature) {
			$strings[] = ' "' . $creature['id'] . '" : { "name" : "' . $creature['name'] . '", "id" : "' . $creature['id'] . '", "thumb" : "' . $creature['thumb'] . '" } ';
		}
		break;

	case 'featured':
		break;

	case 'random':
		$creatures = getAssetsFromQuery('random', 0, 5, 'creature');

		// Prevent browser caching for random entries
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

		foreach ($creatures AS $creature) {
/*			$image = getCreaturePreview($creature['id']);*/
			$strings[] = ' "' . $creature['id'] . '" : { "name" : "' . $creature['name'] . '", "id" : "' . $creature['id'] . '", "thumb" : "' . $creature['thumb'] . '" } ';
		}
		break;
}

header('Content-type: application/x-json');

echo '{ ' . implode(', ', $strings) . ' }';