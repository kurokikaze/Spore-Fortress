<?php

	include_once "./api/spore-api.php";
	include_once "./api/df.php";

	error_reporting(E_ALL);

	/* size table:

	name    goat    dwarf   human

	height  1       1.7     1.9

	size    3       6       7


	*/

	$output = array();

	$creatures = $_GET['creatures'];

	foreach ($creatures AS $creature_id) {
		if (is_numeric($creature_id)) {

			$asset = getInfoPerAsset($creature_id);
			$creature = getCreatureInfo($creature_id);
			$model = getCreatureModel($creature_id);

			$df_creature = new DF_Creature();

			$df_creature->set_name($asset["name"]);
			if (!empty($model['skincolor1'])) {
				$df_color = DwarfFortress::find_nearest_color($model['skincolor1']);
				$df_creature->add_property('color', $df_color); // Color of creature
			} else {
				$df_creature->add_property('color', '7:0:1'); // default to white
			}

			$df_creature->add_property('modvalue', '3'); // value modifier for skin, bones etc
			$df_creature->add_property('homeotherm', 10060 + rand(0, 40)); // homeostasis temperature

			// Population stats
			$df_creature->add_property('cluster_number', '1:4'); // size of herds
			$df_creature->add_property('population_number', '15:30'); // total population cap
			$df_creature->add_property('child', rand(1, 4));

			// Personal Traits
			$df_creature->add_property('speed', (2 + $creature['sprint']) * 200); // speed
			$df_creature->add_property('size', IntVal(4 * $creature['height'] - 1)); // speed

			if (IntVal(4 * $creature['height'] - 1) >= 11) {
				$df_creature->add_property('megabeast');
			} elseif (IntVal(4 * $creature['height'] - 1) >= 9) {
				$df_creature->add_property('semimegabeast');
			}

			$mean_age = round(rand(20, 170));
			$df_creature->add_property('maxage', $mean_age . ':' . $mean_age + 20); // TTL. @todo: more fanciful generation

			$df_creature->add_property('layering', '100'); // How well protected from cold

			// Attacks

			if ($creature['charge'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:kick:kicks:1:' . IntVal($creature['charge']) . ':BLUDGEON');
			}

			if ($creature['bite'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:bite:bites:1:' . IntVal($creature['bite']) . ':BLUDGEON');
			}

			if ($creature['strike'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:strike:strikes:1:' . IntVal($creature['strike']) . ':BLUDGEON');
			}

			if ($creature['spit'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:kick:kicks:1:2:BLUDGEON');
			}

			// Loveablity
			if ($creature['sing'] > 2) {
				$df_creature->add_property('prefstring', 'beautiful songs');
			}

			if ($creature['dance'] > 2) {
				$df_creature->add_property('prefstring', 'lovely dances');
			}

			if ($creature['gesture'] > 2) {
				$df_creature->add_property('prefstring', 'funny gestures');
			}

			if ($creature['posture'] > 2) {
				$df_creature->add_property('prefstring', 'mean poses');
			}

			if ($creature['meanness'] > 70) {
				$df_creature->add_property('prefstring', 'terrifying features');
			}

			// Diet

			if ($creature['carnivore'] > $creature['herbivore']) {
				$df_creature->add_property('carnivore');
			}

			$output[] = $df_creature->get_raws();

			unset($df_creature);
			echo "\n";
		}
	}

	header('Content-type: text');
	header('Content-Disposition: attachment; filename="sporecreatures.txt"');


?>
