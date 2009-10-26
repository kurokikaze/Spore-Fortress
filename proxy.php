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

	case 'search':
		$creatures = getAssetsFromQuery('random', 0, 5, 'creature');
		foreach ($creatures AS $creature) {
/*			$image = getCreaturePreview($creature['id']);*/
			$strings[] = ' "' . $creature['id'] . '" : { "name" : "' . $creature['name'] . '", "id" : "' . $creature['id'] . '", "thumb" : "' . $creature['thumb'] . '" } ';
		}
		break;
}

header('Content-type: application/x-json');

echo '{ ' . implode(', ', $strings) . ' }';