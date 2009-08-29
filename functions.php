<?php

function wpeds_return_curr_timestamp() {
$ts = strtotime(date_i18n(__('Y-m-d G:i:s')));

if ($ts) { return $ts; } else { return time(); }
}

function wpeds_kill_unwanted_fats($str) {
return strip_tags(trim($str));
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

$pattern = '/\<p class="button"\>\<a href=\'(.*)\'\>Download\<\/a\>\<\/p\>/s';
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
'today' => wpeds_kill_unwanted_fats($stat[1][0]),
'yesterday' => wpeds_kill_unwanted_fats($stat[1][1]),
'lastweek' => wpeds_kill_unwanted_fats($stat[1][2]),
'total' => wpeds_kill_unwanted_fats($stat[1][3]),
'dateadded' => $dateadded,
'lastsync' => wpeds_return_curr_timestamp(),
'lastvalues' => $lastvalues,
'lastjump' => $lastjump,
);

return $buildarray;
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


function wpeds_removecomma($numm) {
return str_replace(',','',$numm);
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
$pattern = '/^http:\/\/wordpress.org\/extend\/(.*)\/stats\/$/s';
preg_match($pattern,$url,$match);
if (!empty($match)) {
  return true;
} else {
  return false;
}
}

?>