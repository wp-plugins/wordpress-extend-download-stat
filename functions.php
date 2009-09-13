<?php

function wpeds_return_curr_timestamp() {
$ts = strtotime(date_i18n('Y-m-d G:i:s'));

if ($ts) { return $ts; } else { return time(); }
}

function wpeds_kill_unwanted_fats($str) {
return strip_tags(trim($str));
}

function wpeds_removecomma($numm) {
return str_replace(',','',$numm);
}

function wpeds_getstat($url,$olddata=null) {
$text = file_get_contents($url);

$pattern = '/\<td\>([0-9,]*)\<\/td\>/s';

preg_match_all($pattern,$text,$stat);

//$stat[1][0] = today
//$stat[1][1] = yesterday
//$stat[1][2] = last week
//$stat[1][3] = total

$pattern = '/\<title\>WordPress \&\#8250; (.*) \&laquo; (Free WordPress Themes|WordPress Plugins)\<\/title\>/s';
preg_match($pattern,$text,$iteminfo);

if (substr($iteminfo[2],-1,1) == 's') { $iteminfo[2] = substr($iteminfo[2],0,(strlen($iteminfo[2])-1)); }
$iteminfo[2] = str_ireplace('Free ','',$iteminfo[2]);

//$iteminfo[1] = item title/name
//$iteminfo[2] = type of item

$pattern = '/\<p class="button"\>\<a href=[\'|"](.*)[\'|"]\>Download\<\/a\>\<\/p\>/s';
preg_match($pattern,$text,$downloadlink);

//$downloadlink[1] = download link

$pattern = '/\<li\>\<strong\>Version:\<\/strong\>(.*)\<\/li\>/U';
preg_match($pattern,$text,$version);
//$version[1] = latest version

$pattern = '/\<li\>\<strong\>Last Updated:\<\/strong\>(.*)\<\/li\>/U';
preg_match($pattern,$text,$lastupdate);
//$lastupdate[1] = last update date


if ($iteminfo[1] == NULL || $stat[1][0] == NULL || $downloadlink[1] == NULL) {
return false;
}

$dateadded = time();
if ($olddata) {
  if ($olddata['dateadded'] != '') {
  $dateadded = $olddata['dateadded'];
  }
}

$lastvalues = null;
if ($olddata && is_array($olddata)) {
$lastvalues = $olddata['today'].'/'.$olddata['yesterday'].'/'.$olddata['lastweek'].'/'.$olddata['total'];
}

$lastjump = null;
if ($olddata) {
$lastjump = wpeds_return_curr_timestamp()-$olddata['lastsync'];
}

$buildarray = array(
'name' => $iteminfo[1],
'type' => $iteminfo[2],
'url' => wpeds_kill_unwanted_fats($downloadlink[1]),
'version' => wpeds_kill_unwanted_fats($version[1]),
'lastupdate'=>strtotime(wpeds_kill_unwanted_fats($lastupdate[1])),
'today' => wpeds_kill_unwanted_fats(wpeds_removecomma($stat[1][0])),
'yesterday' => wpeds_kill_unwanted_fats(wpeds_removecomma($stat[1][1])),
'lastweek' => wpeds_kill_unwanted_fats(wpeds_removecomma($stat[1][2])),
'total' => wpeds_kill_unwanted_fats(wpeds_removecomma($stat[1][3])),
'dateadded' => $dateadded,
'lastsync' => wpeds_return_curr_timestamp(),
'lastvalues' => $lastvalues,
'lastjump' => $lastjump,
);

return $buildarray;
}


function wpeds_getuseritems($username) {

$username = strtolower($username);

$buildurl = array(
'http://wordpress.org/extend/plugins/profile/'.$username,
'http://wordpress.org/extend/themes/profile/'.$username
);

$loopid = 1;

foreach ($buildurl as $url) {
if ($loopid == 1) { $itemtype = 'plugins'; } else { $itemtype = 'themes'; }

  $text = file_get_contents($url);
  $pattern = '/\<h3\>\<a href=[\'|"](.*)[\'|"]\>(.*)\<\/a\>\<\/h3\>/U';
  preg_match_all($pattern,$text,$matched);
  
  //$matched[1] = url array
  //$matched[2] = item name array
  
  if (is_array($matched[1]) && count($matched[1])>0) {
    //got item
    $returnarray[$itemtype] = $matched[1];
  } else {
    $returnarray[$itemtype] = array();
  }
  
$loopid++;
}


return $returnarray;
//var_dump($urls[1]);
}


function wpeds_gettimediff($secsdiff,$forceshowtime=null) {
$futureorpast = '';

if ($secsdiff == 0) { // now
$smart = 'Freshest possible';
if ($forceshowtime) { $smart = '0 second'; }
} elseif ($secsdiff < 60) { // this minute
  $smart = $secsdiff.' seconds '.$futureorpast;
  } else { // not this minute
	$minutediff = round($secsdiff/60);
	  if ($minutediff < 60) { // this hour
		$smart = $minutediff;
		$smart .= ($minutediff>1)?' minutes '.$futureorpast:' minute '.$futureorpast;
	  } else { // not this hour
		  $hourdiff = round($minutediff/60);
		  if ($hourdiff < 24) { // this day
			$smart = $hourdiff;
			$smart .= ($hourdiff>1)?' hours '.$futureorpast:' hour '.$futureorpast;
		  } else { // not this day
			$daydiff = round($hourdiff/24);
			if ($daydiff < 31) {
			  $smart = $daydiff;
			  $smart .= ($daydiff>1)?' days '.$futureorpast:' day '.$futureorpast;
			} else {
			  $monthdiff = round($daydiff/31);
				$comparemonth = date("n")-date("n",$stamp);
				if ($comparemonth > 0 && $comparemonth < 12) {
				  $monthdiff = $comparemonth;
				}
			  if ($monthdiff < 12) {
				$smart = $monthdiff;
				$smart .= ($monthdiff>1)?' months '.$futureorpast:' month '.$futureorpast;
			  } else {
				$yeardiff = round($monthdiff/12);
				$smart = $yeardiff;
				$smart .= ($yeardiff>1)?' years '.$futureorpast:' year '.$futureorpast;
			  }
			}
		  }
	  }
  }
 
return $smart;
}


function wpeds_showincrease($thearray,$arraykey) {
global $zv_wpeds_plugin_dir;

if (empty($thearray) || !$thearray) { return; }

  if ($thearray[$arraykey] < 0) {
    return ' (<img title="Decrease" style="vertical-align:middle" src="'.$zv_wpeds_plugin_dir.'images/down.gif" />'.(0-$thearray[$arraykey]).')';
  } elseif ($thearray[$arraykey] > 0) {
    return ' (<img title="Increase" style="vertical-align:middle" src="'.$zv_wpeds_plugin_dir.'images/up.gif" />'.$thearray[$arraykey].')';
  } else {
    return ' (<img title="No change" style="vertical-align:middle" src="'.$zv_wpeds_plugin_dir.'images/same.gif" />)';
  }
}

function wpeds_formaturl($url) {
if (substr($url,0,7) != 'http://') {
$url = 'http://'.$url;
}
if (substr($url,-1,1) != '/') {
$url = $url.'/';
}
return $url;
}


function wpeds_validstaturl($url) {
$url = strtolower($url);                  
$pattern = '/^http:\/\/(www.)?wordpress.org\/extend\/(plugins|themes)\/(.*)\/stats\/$/s';
preg_match($pattern,$url,$match);
if (!empty($match)) {
  return true;
} else {
  return false;
}
}

function wpeds_tt_parse_args($args,$allowedvariable) {
$temp_queryarray = explode('&',$args);

foreach ($temp_queryarray as $single_query) {
  $thepairs = explode('=',$single_query);
    if (!empty($thepairs) && count($thepairs) == 2) {
      if (in_array($thepairs[0],$allowedvariable)) {
        $queryarray[$thepairs[0]] = $thepairs[1];
      }
    }
}

return $queryarray;
}

function wpeds_tt_remove_invalid_args($args,$allowedvariable) {
  foreach ($args as $name => $value) {
    if (!in_array($name,$allowedvariable)) {
      unset($args[$name]);
    }
  }
return $args;
}

function wpeds_apply_format_to_array($array,$type,$numberformat,$dateformat) {

if ($type == 'single' || $type == 'multiple') {} else { return; }

if ($type == 'single') {
  $array['lastupdate'] = date("$dateformat",$array['lastupdate']);
  $array['dateadded'] = date("$dateformat",$array['dateadded']);
  $array['lastsync'] = date("$dateformat",$array['lastsync']);
  $array['today'] = number_format($array['today'],0,'.',$numberformat);
  $array['yesterday'] = number_format($array['yesterday'],0,'.',$numberformat);
  $array['lastweek'] = number_format($array['lastweek'],0,'.',$numberformat);
  $array['total'] = number_format($array['total'],0,'.',$numberformat);  
} else {
  foreach ($array as $url => $data) {
    $array[$url]['lastupdate'] = date("$dateformat",$array[$url]['lastupdate']);
    $array[$url]['dateadded'] = date("$dateformat",$array[$url]['dateadded']);
    $array[$url]['lastsync'] = date("$dateformat",$array[$url]['lastsync']);
    $array[$url]['today'] = number_format($array[$url]['today'],0,'.',$numberformat);
    $array[$url]['yesterday'] = number_format($array[$url]['yesterday'],0,'.',$numberformat);
    $array[$url]['lastweek'] = number_format($array[$url]['lastweek'],0,'.',$numberformat);
    $array[$url]['total'] = number_format($array[$url]['total'],0,'.',$numberformat);  
  }
}

return $array;
}

function wpeds_adaptjs($str) {
$str = str_replace('\'','\\\'',$str);
return $str;
}

?>