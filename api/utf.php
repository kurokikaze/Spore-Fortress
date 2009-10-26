<?php

function isChar($str){
	$charset=false;
	$chars=array('UTF-16','UTF-8','EUC-JP','SJIS');
	if(function_exists("mb_check_encoding")){
		foreach($chars as $order){
			if(mb_check_encoding($str,$order)){
				$charset=$order;
				break;
			}
		}
	} else {
		foreach($chars as $order){
			$from=$order=='UTF-8' ? 'UTF-32' : $order;
			$to=$order=='UTF-32' ? 'UTF-8' : $order;
			if($str === mb_convert_encoding(mb_convert_encoding($str,$from,$to),$to,$from)){
				$charset=$order;
				break;
			}
		}
	}
	return $charset;
}
