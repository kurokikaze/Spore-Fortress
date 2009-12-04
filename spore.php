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

			// This part needs extensive work
			if  ($creature['feet'] == 4) {
				$df_creature->add_body_part('QUADRUPED');
			} elseif ($creature['feet'] == 2 && $creature['hand'] == 4) {
				$df_creature->add_body_part('BASIC_3PARTARMS');
			} elseif ($creature['feet'] == 2) {
				$df_creature->add_body_part('BASIC_3PARTARMS');
			} elseif ($creature['feet'] == 8) {
				$df_creature->add_body_part('SPIDER'); // I know its wrong
			}

			// value modifier for skin, bones etc
			$df_creature->add_property('modvalue', '3');

			// homeostasis temperature
			$df_creature->add_property('homeotherm', 10050 + rand(0, 20));

			// Population stats

			// size of herds
			$df_creature->add_property('cluster_number', '1:4');

			// total population cap on map in a year
			$df_creature->add_property('population_number', '15:30');

			// Reasonable value, but can be bigger for some creatures
			$df_creature->add_property('child', rand(1, 4));

			// Personal Traits
			$df_creature->add_property('speed', (2 + $creature['sprint']) * 200); // speed
			$df_creature->add_property('size', IntVal(4 * $creature['height'] - 1)); // size

			if (IntVal(4 * $creature['height'] - 1) >= 11) {
				$df_creature->add_property('megabeast');
			} elseif (IntVal(4 * $creature['height'] - 1) >= 9) {
				$df_creature->add_property('semimegabeast');
			}

			$mean_age = round(rand(20, 170));
			$df_creature->add_property('maxage', $mean_age . ':' . $mean_age + 20); // TTL. @todo: more fanciful generation

			$df_creature->add_property('layering', '100'); // How well protected from cold

			// Attacks. There s an error here: only 1 attack can be main, i think

			if ($creature['charge'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:kick:kicks:1:' . IntVal($creature['charge']) . ':BLUDGEON');
			}

			if ($creature['bite'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:bite:bites:1:' . IntVal($creature['bite']) . ':BLUDGEON');
			}

			if ($creature['strike'] > 0) {
				$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:strike:strikes:1:' . IntVal($creature['strike']) . ':BLUDGEON');
			}

			// No luck for spitters right now
			// if ($creature['spit'] > 0) {
			// 	$df_creature->add_property('attack', 'MAIN:BYTYPE:STANCE:kick:kicks:1:2:BLUDGEON');
			// }

			// Loveablity
			if ($creature['sing'] > 2) {
				$df_creature->add_property('prefstring', 'beautiful songs');
			}

			if (DwarfFortress::is_color_bright($model['skincolor1'])) {
				$df_creature->add_property('prefstring', 'bright color');
			}

			if ($creature['dance'] > 2) {
				switch (rand(0,2)) {
					case 0:
						$df_creature->add_property('prefstring', 'lovely dances');
						break;
					case 1:
						$df_creature->add_property('prefstring', 'funny dances');
						break;
					case 2:
						$df_creature->add_property('prefstring', 'clumsy dances');
						break;
				}

			}

			if ($creature['height'] >= 4) {
				$df_creature->add_property('prefstring', 'strength');
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
		}
	}

	header('Content-type: text');
	header('Content-Disposition: attachment; filename="sporecreatures.txt"');

	echo implode("\n\n", $output);