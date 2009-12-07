<?php
	abstract class DwarfFortress {
		/**
		* Checking if color is bright enough for dwarves to like
		* Algorithm used: color conversion by Eugene Vishnevsky
		*
		* @link http://www.cs.rit.edu/~ncs/color/t_convert.html
		*
		* @param string $color Color in Maxis format - comma-separated string
		*/
		public function is_color_bright($color) {

			$channels = explode (',' , $color);

			// Spore API stores color channels from 0 to 1
			// This algorithm originally uses same bounds, so no conversions here
			$rgb_color = array(
				'r' => $channels[0],
				'g' => $channels[1],
				'b' => $channels[2]
			);

			$min = min( $rgb_color['r'], $rgb_color['g'], $rgb_color['b'] );
			$max = max( $rgb_color['r'], $rgb_color['g'], $rgb_color['b'] );

			$value = $max;          // Value (brightness-like) component.
									// We also need saturation to separate near-white colors
			$delta = $max - $min;


			if( $max != 0 ) {
				$saturation = $delta / $max;        // s
			} else {
				// On this branch we handle black color. We should set value to -1, but because
				// sat == 0 will lead to same result (color is not bright), we don't bother
				$saturation = 0;
			}

			return $value > 0.5 && $saturation > 0.5;

		}

		public function find_nearest_color($color) {

			$distance = 25000;
			$colors = array(
				// Dim colors - except black, of course
				'1:0:0' => array('r' => 0, 'g' => 0, 'b' => 128),
				'2:0:0' => array('r' => 0, 'g' => 128, 'b' => 0),
				'3:0:0' => array('r' => 0, 'g' => 128, 'b' => 128),
				'4:0:0' => array('r' => 128, 'g' => 0, 'b' => 0),
				'5:0:0' => array('r' => 128, 'g' => 0, 'b' => 128),
				'6:0:0' => array('r' => 128, 'g' => 128, 'b' => 128),
				'7:0:0' => array('r' => 192, 'g' => 192, 'b' => 192),

				// Bright colors
				'0:0:1' => array('r' => 128, 'g' => 128, 'b' => 128),
				'1:0:1' => array('r' => 0, 'g' => 0, 'b' => 255),
				'2:0:1' => array('r' => 0, 'g' => 255, 'b' => 0),
				'3:0:1' => array('r' => 0, 'g' => 255, 'b' => 255),
				'4:0:1' => array('r' => 255, 'g' => 0, 'b' => 0),
				'5:0:1' => array('r' => 255, 'g' => 0, 'b' => 255),
				'6:0:1' => array('r' => 255, 'g' => 255, 'b' => 0),
				'7:0:1' => array('r' => 255, 'g' => 255, 'b' => 255)

			);

			$channels = explode (',' , $color);

			// Spore API stores color channels from 0 to 1
			$target_color = array(
				'r' => $channels[0] * 255,
				'g' => $channels[1] * 255,
				'b' => $channels[2] * 255
			);

			$nearest_color = '2:0:1';

			foreach ($colors AS $df_color_name => $df_color) {
				// compute the Euclidean distance between the two colors
				// note, that the alpha-component is not used in this example
				$dbl_test_red = pow($df_color['r'] - $target_color['r'], 2);
				$dbl_test_green = pow($df_color['g'] - $target_color['g'], 2);
				$dbl_test_blue = pow($df_color['b'] - $target_color['b'], 2);
				// it is not necessary to compute the square root
				// it should be sufficient to use:
				// temp = dbl_test_blue + dbl_test_green + dbl_test_red;
				// if you plan to do so, the distance should be initialized by 250000.0
				$temp = sqrt($dbl_test_blue + $dbl_test_green + $dbl_test_red);
				// explore the result and store the nearest color
				if ($temp == 0.0) {
					// the lowest possible distance is - of course - zero
					// so I can break the loop (thanks to Willie Deutschmann)
					// here I could return the input_color itself
					// but in this example I am using a list with named colors
					// and I want to return the Name-property too
					$nearest_color = $df_color_name;
					break;
				} elseif ($temp < $distance) {
					$distance = $temp;
					$nearest_color = $df_color_name;
				}
			}

			return $nearest_color;
		}
	}

	class DF_Creature {

		private $name;
		// Some stats
		private $stats;
		private $attacks;
		private $body = array('2LUNGS', 'HEART', 'GUTS', 'ORGANS', 'SPINE', 'BRAIN', 'MOUTH'); // Starter set

		private $raws;

		public function add_attack($type, $power) {
			$this->attacks[$type] = $power;
		}

		public function add_body_part($name, $count = 1) {
			$this->body[] = $name;
		}

		public function add_property($property_name, $value = '') {
			$this->raws[] = array($property_name => $value);
		}

		public function set_name($name) {
			if (!empty($name)) {
				$this->name = $name;
			} else {
				throw new Exception("Empty name given");
			}
		}

		// Prepares raws from array of values
		// @todo: make correctness check to avoid errorneous tags
		// @todo: make escaping of token values
		public function get_raws() {

			$raw_object = '';

			$raw_object .= '[CREATURE:'. strtoupper(str_replace(' ', '_', $this->name)) . ']' . "\n";
			$raw_object .= "\t" . '[NAME:' . $this->name . ':' . $this->name .'s:'. $this->name . ']' . "\n";
			$raw_object .= "\t" . '[TILE:' . substr($this->name, 0, 1) . ']' . "\n";
			$raw_object .= "\t" . '[STANDARD_FLESH]' . "\n";
			$raw_object .= "\t" . '[LARGE_ROAMING]' . "\n";

			$attacks = $this->attacks;

			if (is_array($attacks) && !empty($attacks)) {
				arsort($attacks, true);
				if (count($attacks) > 2) {
					$attacks = array_slice($attacks, 0, 2, true); // Only first attacks
				}

				$numbering = array(
					0 => 'MAIN',
					1 => 'SECOND',
					2 => 'THIRD'
				);

				$i = 0;

				$battle_stack = '';

				// print_r($attacks);

				foreach ($attacks as $type => $power) {
					switch ($type) {
					   case 'kick':
						 $attack_text = 'kick:kicks';
						 $attack_type = 'BLUDGEON';

						 break;
					   case 'charge':
						 $attack_text = 'charge:charges';
						 $attack_type = 'BLUDGEON';

						 break;
					   case 'bite':
						 $attack_text = 'bite:bites';
						 $attack_type = 'MOUTH';

						 break;
					   case 'strike':
						 $attack_text = 'strike:strikes';
						 $attack_type = 'GRASP';

						 break;

					   default:
						 continue;
						 break;
					}

					if ($power > 0) {
						$attack_number = $numbering[$i];

						$attack_record = '[ATTACK:' . $attack_number . ':BYTYPE:' . $attack_type . ':' . $attack_text . ':1:' . $power . ':BLUDGEON]';

						$battle_stack .= $attack_record . "\n";

						$i++;
					}
				}
			}

			$raw_object .= '[BODY:' . implode(':', $this->body) . ']' . "\n";

			// Add attacks
			$raw_object .= $battle_stack;

			foreach($this->raws AS $property) {

				foreach ($property AS $token => $value) {
					// Add tabulation before creature tokens
					$break = '';
					if (strtoupper($token) != 'CREATURE') {
						$break = "\t";
					}

					$raw_object .= $break . '[' . strtoupper($token) . (($value!='')?':'  . $value:'') . ']' . "\n";
				}
			}


			return $raw_object;
		}
	}