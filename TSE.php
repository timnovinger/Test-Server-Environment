<?php
/* =====================================================================================
PHP SERVER MODULE REPORTER

Written By: 	David Walsh :: david@davidwalsh.name :: Twitter { davidwalshblog }
http://blog.jeremymartin.name/2008/04/know-your-environment-php-server-module.html

Improvements — GeoIP lookups, mapping, additions to CSS styles
Written By: 	Tim Novinger :: tim.novinger@gmail.com :: Twitter { timnovinger }
Last Update:	04.22.09
test
======================================================================================== */

// set required extensions
$my_required_extensions = array(
	'gd',  //graphics library
	'xml',  //xml
	'mysql', //database
	'curl',  //networking
	'openssl', //site will need SSL
	'pecl'  //pear
);
natcasesort($my_required_extensions);

//get loaded modules
$loaded_extensions = get_loaded_extensions();

//use cURL to reverse lookup IP addresses
function geolocate($uri){  
	$ch = curl_init();  
	$timeout = 5;  
	curl_setopt($ch,CURLOPT_URL,'http://api.hostip.info/get_html.php?ip='.$uri.'&position=true');  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
	$content = curl_exec($ch);  
	curl_close($ch);
	
	return $content;  
}

//parse out Longitude and Latitude from hostip.info returned results	
function parse_latlong($str){
	return str_replace('ong', ',', ereg_replace("[\r\n\t\v\ Latitude: ]", "", strstr($str, "Latitude: ")));
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Server Environment:: <?=$_SERVER['HTTP_HOST']?></title>
	<style type="text/css">
		body{ font-family: "Lucida Sans", Helvetica, Arial; font-size:12px; line-height:16px; }
		h1{ font-size:30px; }
		h2{ background:lightyellow; border:2px solid yellow; padding:5px 10px; font-weight:normal; font-size:18px; margin-bottom:5px;}
		p{ line-height:20px; margin-top:0; }
		.found{ background:lightgreen; padding:5px 10px; margin:0 0 10px 0; }
		.miss{ background:pink; padding:5px 10px; margin:0 0 10px 0; }
		.extra{ background:lightblue; padding:5px 10px; margin:0 0 10px 0; }
		.rtt{text-align:right; width:100%;}
		a{color:#333;}
		#wrapper{position:relative; margin:20px auto; width:870px;}
		blockquote{position:relative; padding:0; margin:5px 0; line-height:18px;}
	</style>
</head>
<body id="top">
<div id="wrapper">
	<h1>Server Environment:: <?=$_SERVER['HTTP_HOST']?></h1>
	
	<h2>General Information</h2>
	<p>
		<strong>Server Hostname:</strong> <?=$_SERVER['HTTP_HOST']?><br />
		<strong>Server Admin Email:</strong>  <a href="mailto:<?=$_SERVER['SERVER_ADMIN']?>"><?=$_SERVER['SERVER_ADMIN']?></a><br />
		<strong>Server IP Address:</strong> <?=$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']?>
	</p>
	<blockquote>
		<?php
			echo nl2br(geolocate(gethostbyname($_SERVER['HTTP_HOST'])));
			$ll = parse_latlong(geolocate(gethostbyname($_SERVER['HTTP_HOST'])));
		?>
		<br />
		>> <a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?=$ll?>&amp;sll=<?=$ll?>&amp;sspn=&amp;ie=UTF8&amp;z=12&amp;iwloc=A" target="_blank" title="View Using Google Maps">Map It!</a>
		<br /><br />
	</blockquote>
	<p>
		<strong>Server Software:</strong><br /><?=$_SERVER['SERVER_SOFTWARE']?><br /><br />
		<strong>Document Root:</strong> <?=$_SERVER['DOCUMENT_ROOT']?><br />
		<strong>PHP Version:</strong> <?=phpversion()?><br />
		<br /><br />
	</p>
	
	<h2>Visitor Information</h2>
	<p>
		<strong>Web Browser:</strong> <?=$_SERVER['HTTP_USER_AGENT']?><br />
		<strong>Visitor IP Address:</strong> <?=$_SERVER['REMOTE_ADDR']?><br />
		<strong>Visitor Hostname:</strong> <?=gethostbyaddr($_SERVER['REMOTE_ADDR'])?>
	</p>
 	<blockquote>
		<?php
			echo nl2br(geolocate(gethostbyname($_SERVER['REMOTE_ADDR'])));
			$ll = parse_latlong(geolocate(gethostbyname($_SERVER['REMOTE_ADDR'])));
		?>
		<br />
		>> <a href="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?=$ll?>&amp;sll=<?=$ll?>&amp;sspn=&amp;ie=UTF8&amp;z=12&amp;iwloc=A" target="_blank" title="View Using Google Maps">Map It!</a>
	 <br /><br />
	</blockquote>
	
	<h2>Extension Check</h2>
	<?php 
		/* print out */
		//analyze results
		foreach($my_required_extensions as $ext)
		{
			if(in_array($ext,$loaded_extensions)){
				$matches[] = strtolower($ext);
			} else {
				$missings[] = strtolower($ext);
			}
			unset($loaded_extensions[$ext]);
		}
		//print out results
		natcasesort($matches); 
		natcasesort($missings); 
		natcasesort($loaded_extensions);
		
		if(count($matches)) {
			foreach($matches as $match) { echo '<div class="found"><strong>',$match,'</strong> found!</div>'; }
		}
		if(count($missings)) {
			foreach($missings as $miss) { echo '<div class="miss"><strong>',$miss,'</strong> missing!</div>'; }
		}
		if(count($loaded_extensions)) {
			foreach($loaded_extensions as $e) 
			{ 
				if(!in_array($e,$matches) && !in_array($e,$missings)) { 
					echo '<div class="extra"><strong>',$e,'</strong> is available.</div>'; 
				} 
			}
		}
	?>
	<p class="rtt"><a href="#top">return to top</a></p>
</div>
</body>
</html>