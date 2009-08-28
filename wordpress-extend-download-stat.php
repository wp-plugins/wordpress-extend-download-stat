<?php
/*
 * Plugin Name: Wordpress Extend Download Stat
 * Plugin URI: http://zenverse.net/wordpress-extend-download-stat-plugin/
 * Description: Sometimes you need to display the number of downloads of your plugin or theme hosted by wordpress, Wordpress Extend Download Stat can retrieve it for you. The retrieved data will be stored on your local server and you decide when it should re-synchronize the data.
 * Author: Zen
 * Author URI: http://zenverse.net/
 * Version: 1.0
*/

/*
 * USAGE:
 *
 * To output download stat, use shortcode [downloadstat] in your post content / excerpt.
 * To make the process easier, you can use the media button (see screenshot) and follow the steps given.
 * 
 * --------------------
 * Get Single Info Only
 * --------------------
 * ##### use attribute 'get'
 * ##### valid values of attribute 'get'
 *       = all tags available (more info at http://zenverse.net/wordpress-extend-download-stat-plugin/#tags)
 * 
 * 
 * Example:
 * XXX has been downloaded [downloadstat url="URL_TO_XXX_STATISTIC_PAGE" get="total" autop="false"] times in total
 * XXX has been downloaded [downloadstat url="URL_TO_XXX_STATISTIC_PAGE" get="today" autop="false"] times today
 * <a href="[downloadstat url="URL_TO_XXX_STATISTIC_PAGE" get="url" autop="false"]">Download XXX</a>
 * 
 * 
 * 
 * -----------------------------------------------------
 * Get Formatted Info - return output based on format id
 * -----------------------------------------------------
 * ##### use attribute 'format'
 * ##### you can create and save a new format at plugin option page
 * ##### default format has id = 0 , which is: <a href="{url}">{name}</a> has been downloaded {total} times in total
 * 
 * Example:  
 * 
 * Display using default format (you don't have to specify the format id)
 * [downloadstat url="URL_TO_THE_STATISTIC_PAGE"] 
 * 
 * Display using format id 1
 * [downloadstat url="URL_TO_THE_STATISTIC_PAGE" format="1"] 
 * 
 * 
 * -----------------------------------------------------
 * Auto wrap output content with HTML paragraph <p> tag
 * -----------------------------------------------------
 * ##### use attribute 'autop'
 * ##### by default autop is set to true, which means it automatically wrap the output content with &lt;p> tags
 * ##### To display the content inline, use autop="false" 
 *   
*/

// Pre 2.6 compatibility (BY Stephen Rider)
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	if ( defined( 'WP_SITEURL' ) ) define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
	else define( 'WP_CONTENT_URL', get_option( 'url' ) . '/wp-content' );
}


$zv_wpeds_plugin_name = 'Wordpress Extend Download Stat';
$zv_wpeds_plugin_dir = WP_CONTENT_URL.'/plugins/wordpress-extend-download-stat/';
$zv_wpeds_siteurl = get_option('siteurl');
$zv_wpeds_plugin_ver = '1.0';
$zv_wpeds_plugin_url = 'http://zenverse.net/wordpress-extend-download-stat-plugin/';
$zv_wpeds_default_format = '<a href="{url}">{name}</a> has been downloaded {total} times in total.';
$zv_wpeds_urltoautosync = null;
$zv_wpeds_dateformat_db = array ("d F Y",'d M Y','d-m-Y','d/m/Y',"d F Y g.i A",'d M Y g.i A','d-m-Y g.i A','d/m/Y g.i A',);

require_once('functions.php');


function wpeds_shortcode($atts) {
global $zv_wpeds_default_format,$zv_wpeds_urltoautosync,$zv_wpeds_dateformat_db;

	extract(shortcode_atts(array(
		'url' => '',
		'get' => 'total',
		'format' => '0',
		'autop' => 'true',
	), $atts));

  $output = '';
  $needresync = false;
  
  $wpeds_data = get_option('wpeds_data');
  $wpeds_options = get_option('wpeds_options');
  
  $usedateformat = $zv_wpeds_dateformat_db[0];
  if (!empty($wpeds_options) && isset($wpeds_options['dateformat']) && $wpeds_options['dateformat']!='') {
    if (in_array($wpeds_options['dateformat'],$zv_wpeds_dateformat_db)) {
    $usedateformat = $wpeds_options['dateformat'];
    }
  }
  
  //$usedateformat = 'd F Y';
  
  //echo '<pre>';
  //var_dump($wpeds_data);
  
  if (empty($wpeds_data) || $wpeds_data=='') {
  $wpeds_data = array();
  }
  
  if ($atts['url'] == '') {//url not specified
    return '';
  } else {
    $atts['url'] = wpeds_formaturl($atts['url']);
    
      if (!wpeds_validstaturl($atts['url'])) { return ''; }
    
    if (!isset($wpeds_data[$atts['url']])) {//cant find in stored data
    $getallstat = wpeds_getstat($atts['url']);
    $wpeds_data[$atts['url']] = $getallstat;
      if ($getallstat) {
      //save data
      update_option('wpeds_data',$wpeds_data);
      }
    } else {
    $getallstat = $wpeds_data[$atts['url']];
      //need auto sync??
      if (!empty($wpeds_options) && isset($wpeds_options['autosynctime']) && is_numeric($wpeds_options['autosynctime'])) {
        if ((wpeds_return_curr_timestamp() - $getallstat['lastsync']) >= $wpeds_options['autosynctime']) {
          $needresync = true;
        }
      } else {
        if ((wpeds_return_curr_timestamp() - $getallstat['lastsync']) >= 86400) {
          $needresync = true;
        }
      }
      
      if ($wpeds_options['autosynctime'] == 0) { $needresync = false; }
      
      if ($needresync) {
        $zv_wpeds_urltoautosync[] = $atts['url'];
        add_action('wp_footer', 'wpeds_wpfooter');
        //return 'need update';
      }
    }
  }
  
  $isformattedinfo = true;
  
  if ($atts['format'] == '') {
    if ($atts['get'] != '') {
      $isformattedinfo = false;
    }
  }
  
  //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  $tobereplaced = array('{name}','{type}','{today}','{yesterday}','{lastweek}','{total}','{lastsync}','{url}','{dateadded}','{freshness}','{version}','{lastupdate}');
  $tobereplaced_single = array('name','type','today','yesterday','lastweek','total','lastsync','url','dateadded','freshness','version','lastupdate');  
  $replacement = array($getallstat['name'],$getallstat['type'],$getallstat['today'],$getallstat['yesterday'],$getallstat['lastweek'],$getallstat['total'],date("$usedateformat",$getallstat['lastsync']),$getallstat['url'],date("$usedateformat",$getallstat['dateadded']),wpeds_gettimediff(wpeds_return_curr_timestamp()-$getallstat['lastsync']),$getallstat['version'],date("$usedateformat",$getallstat['lastupdate']));
  //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  
  if (!$isformattedinfo) {// single info
    //$allowed_value = array('total','today','yesterday','lastweek','url','version','type','lastupdate',);
    //if (!in_array($atts['get'],$allowed_value)) { $atts['get'] = 'total'; }
    //$output = $getallstat[$atts['get']];
    $formatused = $atts['get'];
    $output = str_ireplace($tobereplaced_single,$replacement,$formatused);
    
    if ($output == $formatused) {//not replaced, not in allowed array
      $output = '';
    }
    
  } else {
  //formatted info

    if (!is_numeric($atts['format']) || $atts['format'] <= 0 ) {
    $formatused = $zv_wpeds_default_format;
    } else {
      $wpeds_formats = get_option('wpeds_formats');
      if ($wpeds_formats == '' || empty($wpeds_formats) || !isset($wpeds_formats[$atts['format']]) || $wpeds_formats[$atts['format']]['format'] == '') {
      $formatused = $zv_wpeds_default_format;
      } else {
      $formatused = $wpeds_formats[$atts['format']]['format'];
      }
    }
    $output = str_ireplace($tobereplaced,$replacement,$formatused);
  }
 
  if ($atts['autop'] != 'false') {
    $output = wpautop($output);
  }
 
	return stripslashes($output);
}

add_shortcode('downloadstat', 'wpeds_shortcode');
add_filter('the_content', 'do_shortcode', 11);
add_filter('the_excerpt', 'do_shortcode', 11);


######## LOAD AUTO SYNC #######

$wpeds_plugin_adminhead = false;

$wpeds_options = get_option('wpeds_options');
if (!empty($wpeds_options) && isset($wpeds_options['autosync_situation'])) {
switch ($wpeds_options['autosync_situation']) {
case 'blog':
  add_action('wp_head', 'wpeds_wphead');
break;
case 'admin':
  add_action('admin_head', 'wpeds_wphead');
break;
case 'plugin':
  $wpeds_plugin_adminhead = true;
break;
case 'all':
  add_action('wp_head', 'wpeds_wphead');
  add_action('admin_head', 'wpeds_wphead');
break;
}
}

######## ADMIN PANEL #######

/* admin menu */
add_action('admin_menu', 'wpeds_menu');

function wpeds_menu() {
global $zv_wpeds_plugin_name,$wpeds_plugin_adminhead;
$plugin_page = add_options_page($zv_wpeds_plugin_name, 'WP Ex Download Stat', 8, __FILE__, 'wpeds_options');
if ($wpeds_plugin_adminhead) { add_action('admin_head-'.$plugin_page, 'wpeds_wphead' ); }
}

function wpeds_options() {
global $zv_wpeds_plugin_name,$zv_wpeds_plugin_ver,$zv_wpeds_plugin_url,$zv_wpeds_siteurl,$zv_wpeds_plugin_dir,$zv_wpeds_dateformat_db;

$wpeds_data = get_option('wpeds_data');
$wpeds_formats = get_option('wpeds_formats');
$wpeds_options = get_option('wpeds_options');
$autorefreshmsg = ' <a href="'.$zv_wpeds_siteurl.'/wp-admin/options-general.php?page=wordpress-extend-download-stat/wordpress-extend-download-stat.php" style="color:red" title="Click here if the auto refresh does not work">Auto Refreshing...</a><script type="text/javascript">
setTimeout("location.href=\''.$zv_wpeds_siteurl.'/wp-admin/options-general.php?page=wordpress-extend-download-stat/wordpress-extend-download-stat.php\';",1500);
</script>';
//$autorefreshmsg = '';
$autosync_db = array('0'=>'They never outdate. I will synchronize them manually.','3600'=>'1 Hour','43200'=>'12 Hours','86400'=>'1 Day','259200'=>'3 Days','604800'=>'1 Week','2678400'=>'1 Month','15768000'=>'6 Months','31536000'=>'1 Year');
$zv_wpeds_autosync_situations_db = array (0=>'Never. Check only when that data is needed. (default, recommended)','blog'=>'Only when visitors are surfing the blog. (might increase server load)','admin'=>'Only when visitors are surfing the admin panel. (might increase server load)','plugin'=>'Only when visitors are surfing the plugin option page - this page.','all'=>'At all places. (might increase server load)',);


if (isset($_POST['wpeds_delete'])) {
  if ($_POST['wpeds_url'] != '') {
    if (isset($wpeds_data[$_POST['wpeds_url']])) {
      unset($wpeds_data[$_POST['wpeds_url']]);
      //save data
      update_option('wpeds_data',$wpeds_data);
      echo '<div class="updated" style="padding:5px;"><b>The data of <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small> has been deleted.'.$autorefreshmsg.'</b></div>';
    } else {
      echo '<div class="updated" style="padding:5px;"><b>No data found for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small></b></div>';
    }
  }
}


if (isset($_POST['wpeds_syncnew'])) {
  if ($_POST['wpeds_url'] != '') {
    $_POST['wpeds_url'] = wpeds_formaturl($_POST['wpeds_url']);
    if (isset($wpeds_data[$_POST['wpeds_url']])) {//data already exist, pass to resync
      $_POST['wpeds_resync'] = '1';
    } else {
    if (!wpeds_validstaturl($_POST['wpeds_url'])) {
    echo '<div class="updated" style="padding:5px;"><b>Invalid URL to statistics page. <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small></b></div>';
    } else {
      $getallstat = wpeds_getstat($_POST['wpeds_url']);
      $wpeds_data[$_POST['wpeds_url']] = $getallstat;
        if ($getallstat) {
        //update data
        update_option('wpeds_data',$wpeds_data);
        echo '<div class="updated" style="padding:5px;"><b>Data has been successfully loaded for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small>'.$autorefreshmsg.'</b></div>';
        } else {
        echo '<div class="updated" style="padding:5px;"><b>Error. Invalid data for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small></b></div>';
        }      
    }
    }
  }
}


if (isset($_POST['wpeds_resync'])) {

  if ($_POST['wpeds_url'] != '') {

    $olddata = null;
    if (isset($wpeds_data[$_POST['wpeds_url']])) {
    $olddata = $wpeds_data[$_POST['wpeds_url']];
    }
    
    $getallstat = wpeds_getstat($_POST['wpeds_url'],$olddata);
    $wpeds_data[$_POST['wpeds_url']] = $getallstat;

    //if (isset($wpeds_data[$_POST['wpeds_url']])) {
    //  unset($wpeds_data[$_POST['wpeds_url']]);    
    //}
    
    $nochange = false;    
    if ($olddata) {
    if ($olddata['today'].'/'.$olddata['yesterday'].'/'.$olddata['lastweek'].'/'.$olddata['total'] == $getallstat['today'].'/'.$getallstat['yesterday'].'/'.$getallstat['lastweek'].'/'.$getallstat['total']) {
      $nochange = true;
    }
    }

    if ($nochange) { //no changes
      echo '<div class="updated" style="padding:5px;"><b>No changes detected for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small>'.$autorefreshmsg.'</b></div>';
    } else {
      if ($getallstat) {
      //update data
      update_option('wpeds_data',$wpeds_data);
      echo '<div class="updated" style="padding:5px;"><b>Data has been successfully updated for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small>'.$autorefreshmsg.'</b></div>';
      } else {
      echo '<div class="updated" style="padding:5px;"><b>Error. Invalid data for <small style="color:#3A81AD">&lt; '.$_POST['wpeds_url'].' ></small></b></div>';
      }
    }

  unset($getallstat);    unset($olddata);  
  }
}


if (isset($_POST['wpeds_editformat'])) {
  if ($_POST['wpeds_formattags'] != '') {
    if ($_POST['wpeds_formatid'] != '' && is_numeric($_POST['wpeds_formatid'])) {
      if (isset($wpeds_formats[$_POST['wpeds_formatid']])) {
      $wpeds_formats[$_POST['wpeds_formatid']]= array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
      //update data
      update_option('wpeds_formats',$wpeds_formats);      
      echo '<div class="updated" style="padding:5px;"><b>The format of id '.$_POST['wpeds_formatid'].' has been updated.</small></b></div>';
      } else {//pass to addformat
      $_POST['wpeds_addformat'] = 1;
      }
    } else {
      echo '<div class="updated" style="padding:5px;"><b>Format id was not specified. Please submit the form properly.</small></b></div>';
    }
  } else {
    echo '<div class="updated" style="padding:5px;"><b>The format cannot be empty.</small></b></div>';
  }
}


if (isset($_POST['wpeds_addformat'])) {
  if ($_POST['wpeds_formattags'] != '') {
    if (empty($wpeds_formats)) {
      $wpeds_formats[1] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
    } else {
      ksort($wpeds_formats);
      $allkeys = array_keys($wpeds_formats);
      $countkeys = count($allkeys);
      $getlastkey = $allkeys[($countkeys-1)];
      $wpeds_formats[($getlastkey+1)] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
    }
      //update data
      update_option('wpeds_formats',$wpeds_formats);
      echo '<div class="updated" style="padding:5px;"><b>The new format has been added.</small>'.$autorefreshmsg.'</b></div>';
  } else {
    echo '<div class="updated" style="padding:5px;"><b>Error. The format is empty.</small></b></div>';
  }
}


if (isset($_POST['wpeds_deleteformat'])) {
  if ($_POST['wpeds_formatid'] != '' && is_numeric($_POST['wpeds_formatid'])) {
    if (isset($wpeds_formats[$_POST['wpeds_formatid']])) {
    $wpeds_formats[$_POST['wpeds_formatid']] = array('name'=>'','format'=>'');
    //update data
    update_option('wpeds_formats',$wpeds_formats);
    echo '<div class="updated" style="padding:5px;"><b>Format id '.$_POST['wpeds_formatid'].' has been deleted.</small></b></div>';
    } else {
    echo '<div class="updated" style="padding:5px;"><b>Delete failed. The format does not exist.</small></b></div>';
    }
  } else {
    echo '<div class="updated" style="padding:5px;"><b>Format id was not specified. Please submit the form properly.</small></b></div>';
  }
}


if (isset($_POST['wpeds_saveoptions'])) {
  if (isset($autosync_db[$_POST['wpeds_autosynctime']])) {
  $wpeds_options['autosynctime'] = $_POST['wpeds_autosynctime'];
  } else {
  $wpeds_options['autosynctime'] = 86400;
  }
  
  if (in_array($_POST['wpeds_dateformat'],$zv_wpeds_dateformat_db)) {
  $wpeds_options['dateformat'] = $_POST['wpeds_dateformat'];
  } else {
  $wpeds_options['dateformat'] = $zv_wpeds_dateformat_db[0];
  }

  if (isset($zv_wpeds_autosync_situations_db[$_POST['wpeds_autosync_situation']])) {
  $wpeds_options['autosync_situation'] = $_POST['wpeds_autosync_situation'];
  } else {
  $wpeds_options['autosync_situation'] = $zv_wpeds_autosync_situations_db[0];
  }
  
  
  //update data
  update_option('wpeds_options',$wpeds_options);
  echo '<div class="updated" style="padding:5px;"><b>Plugin Options has been updated.</small></b></div>';
}


if (isset($_POST['wpeds_resetoptions'])) {
  if (!empty($wpeds_options)) {
  delete_option('wpeds_options');
  unset($wpeds_options);
  }
  echo '<div class="updated" style="padding:5px;"><b>Plugin Options has been resetted.</small></b></div>';
}


//$wpeds_data['http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/stats/']['lastsync'] = wpeds_return_curr_timestamp();
//update_option('wpeds_data',$wpeds_data);

?>
<div class="wrap">
<?php screen_icon(); 
$h1style = 'style="background-image:url('.$zv_wpeds_plugin_dir.'images/titleimg.jpg);" class="wpeds_css_optionh1"';
?>
<h2><?php echo wp_specialchars($zv_wpeds_plugin_name); ?></h2>
</div>

<div class="updated" style="display:none;padding:5px;font-weight:bold" id="wpeds_resync_status_div"></div>

<div style="padding:10px;border:1px solid #dddddd;background-color:#fff;-moz-border-radius:10px;margin-top:20px;margin-bottom:20px;">
<?php
echo 'Version '.$zv_wpeds_plugin_ver.' | <a href="'.$zv_wpeds_plugin_url.'">Plugin How-to, FAQs, Change Log & Info</a> | <strong><a href="http://zenverse.net/support/">Donate</a></strong> | <a href="http://zenverse.net/">by ZENVERSE</a>';
?>
</div>

<!-- -->

<h1 <?php echo $h1style; ?>><a onclick="wpeds_toggle('wpeds_oneblock_options')">Plugin Options</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_options">
  <form method="post" action="">
    
    <div class="wpeds_css_optionblock">
    <strong>Definition of "Outdated Data"</strong> <select name="wpeds_autosynctime">
    <?php    
    foreach ($autosync_db as $timeframe => $text) {
    echo '<option value="'.$timeframe.'"';
      if (!empty($wpeds_options)) { 
        if ($wpeds_options['autosynctime'] == $timeframe) { echo ' selected="selected"'; }
      } else {
        if ($timeframe=='86400') { echo ' selected="selected"'; }        
      }
    echo '>'.$text.'</option>';
    }
    
    ?>
    </select>
    <br />
    <small>A data is considered as outdated when its freshness has reached {your selected timeframe}</small>
    </div>
    
    
    <div class="wpeds_css_optionblock">
    <strong>Auto Check for Outdated Data</strong> <select name="wpeds_autosync_situation">
    <?php
      foreach ($zv_wpeds_autosync_situations_db as $id => $value) {
        echo '<option value="'.$id.'"';
        if ($wpeds_options['autosync_situation'] == $id) { echo ' selected="selected"'; }
        echo '>'.$value.'</option>';
      }
    ?>
    </select><br />
    <small>
    &raquo; We can't run the resynchronization automatically, so we need to check for outdated data manually EVERYTIME someone visits your site.<br />
    &raquo; This might increase server load, therefore it's best to choose the first option.<br />
    &raquo; "blog" means that `wp_head` will be used to load the ajax that initiate the resync process.<br />
    &raquo; "admin panel" means that `admin_head` will be used to load the ajax that initiate the resync process.<br />
    &raquo; You can ignore this part if you disabled auto synchronize at above.<br />
    </small>
    </div>
    
    
    <div class="wpeds_css_optionblock">
    <strong>Date Format</strong> <select name="wpeds_dateformat">
    <?php
      foreach ($zv_wpeds_dateformat_db as $id => $value) {
        echo '<option value="'.$value.'"';
        if ($wpeds_options['dateformat'] == $value) { echo ' selected="selected"'; }
        echo '>'.date($value).'</option>';
      }
    ?>
    </select>
    <br />
    <small>Format for all date-related output. For {dateadded}, {lastsync} and {lastupdate} tags. If you want to show freshness, use {freshness} tag instead.</small>
    </div>
  

  <input type="submit" name="wpeds_saveoptions" class="button-primary" value="Save Option" /> 
  <input type="submit" name="wpeds_resetoptions" class="button" value="Reset to default" onclick="return confirm('Are you sure you want to reset the plugin options?')" />
  </form>
</div>

<!-- -->

<h1 <?php echo $h1style; ?>><a onclick="wpeds_toggle('wpeds_oneblock_saveddata')">Saved Data</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_saveddata">

<script type="text/javascript">
<!--
document.write('<div style="background-image:url(<?php echo $zv_wpeds_plugin_dir; ?>images/grad.gif);" class="wpeds_css_saveddata_jscontrol"><strong>Show</strong> : <a onclick="wpeds_limitresult(\'all\')" id="wpeds_saveddata_jscontrol_text_all" style="border-bottom:1px solid #888888">All (<span id="wpeds_numsaveddata_all"></span>)</a> <i>|</i> <a onclick="wpeds_limitresult(\'plugins\')" id="wpeds_saveddata_jscontrol_text_plugins">Plugins (<span id="wpeds_numsaveddata_plugins"></span>)</a> <i>|</i> <a onclick="wpeds_limitresult(\'themes\')" id="wpeds_saveddata_jscontrol_text_themes">Themes (<span id="wpeds_numsaveddata_themes"></span>)</a></div>');
//-->
</script>

<div style="clear:both"></div>
<?php

//echo '<pre>';
//var_dump($wpeds_data);

  if ($wpeds_data=='' || empty($wpeds_data)) {
    echo 'No saved data.';
  } else {
    $swapcolours = 'fff';
    $loopid = 1;
    $numofthemes = 0; $numofplugins = 0;
    
    foreach ($wpeds_data as $url => $data) {

      if ($data['type']=='WordPress Plugin') { $numofplugins++; $typestr = 'plugins'; }
      if ($data['type']=='WordPress Theme') { $numofthemes++; $typestr = 'themes'; }
      
      if ($data['lastvalues'] != '') {
      $lastvalues_js_add = '<script type="text/javascript">
        <!--
        document.write(\'<small style="float:right">[<a title="Changes compared to last synchronized data" style="color:#d7225e" href="javascript:void(0)" onclick="wpeds_showhide_lastdatadiv(\\\''.$loopid.'\\\'); if (this.innerHTML == \\\'Show Changes\\\') { this.innerHTML = \\\'Hide Changes\\\' } else { this.innerHTML = \\\'Show Changes\\\' } ">Show Changes</a>]</small>\');
        //-->
        </script>';
      } else { $lastvalues_js_add = ''; }
      
      // start output
      echo '<div id="wpeds_saveddata_item_'.$loopid.'" style="padding:7px;background:#'.$swapcolours.';border:1px dotted #aaaaaa;margin-bottom:10px;"><div style="display:none" id="wpeds_saveddata_itemtype_'.$loopid.'">'.$typestr.'</div>';

      if ($data['lastvalues'] != '') {
      $data2 = explode('/',$data['lastvalues']);
      $increase_array = array(wpeds_removecomma($data['today'])-wpeds_removecomma($data2[0]),wpeds_removecomma($data['yesterday'])-wpeds_removecomma($data2[1]),wpeds_removecomma($data['lastweek'])-wpeds_removecomma($data2[2]),wpeds_removecomma($data['total'])-wpeds_removecomma($data2[3]));
      } else {
      $data2 = $increase_array = array();
      }
        
      echo '     
      <table class="widefat" style="float:right;width:35%;">
        <thead><tr><th colspan="3">Download Stats'.$lastvalues_js_add.'</th></tr></thead>
        <tr><td width="70" style="border-right:1px solid #dddddd;">Total</td><td>'.$data['total'].'</td><td class="wpeds_css_hiddentd" id="wpeds_lastdatadiv1_'.$loopid.'">'.$data2[3].wpeds_showincrease($increase_array,'3').'</td></tr>
        <tr><td style="border-right:1px solid #dddddd;">Today</td><td>'.$data['today'].'</td><td class="wpeds_css_hiddentd" id="wpeds_lastdatadiv2_'.$loopid.'">'.$data2[0].wpeds_showincrease($increase_array,'0').'</td></tr>
        <tr><td style="border-right:1px solid #dddddd;">Yesterday</td><td>'.$data['yesterday'].'</td><td class="wpeds_css_hiddentd" id="wpeds_lastdatadiv3_'.$loopid.'">'.$data2[1].wpeds_showincrease($increase_array,'1').'</td></tr>
        <tr><td style="border-right:1px solid #dddddd;">Last Week</td><td>'.$data['lastweek'].'</td><td class="wpeds_css_hiddentd" id="wpeds_lastdatadiv4_'.$loopid.'">'.$data2[2].wpeds_showincrease($increase_array,'2').'</td></tr>';
        
      echo '<tr><td colspan="3"><small id="wpeds_lastdatadiv5_'.$loopid.'" class="wpeds_css_hiddenspan">Time difference between datas : '.wpeds_gettimediff($data['lastjump'],true).'</small></td></tr></table>
        
      <table style="width:60%;float:left;"><tr><td>
      <p><strong class="wpeds_css_saveddata_title">'.$data['name'].'</strong></p>

      <p>
      <ul class="wpeds_css_saveddata_ul">
      <li><b>Type</b> &nbsp;'.$data['type'].'</li>
      <li><b>Latest Version</b> &nbsp;'.$data['version'].'</li>
      <li><b>Last Update</b> &nbsp;'.date("d F Y",$data['lastupdate']).'</li>
      <li><b>Freshness</b> &nbsp;'.wpeds_gettimediff(wpeds_return_curr_timestamp()-$data['lastsync']).'</li>
      <li style="border:0px"><b>Download</b> &nbsp;'.$data['url'].'</li>
      </ul>
      </small></p>
        <form method="post" action="">
        <input type="submit" class="button-primary" name="wpeds_resync" value="Re-sync" />
        <input type="submit" class="button" onclick="return confirm(\'Are you sure you want to delete the data of '.$data['name'].'?\');" name="wpeds_delete" value="Delete" />
        <input type="hidden" name="wpeds_url" value="'.$url.'" />
        </form>
      <p><small></small></p>
      <p><small>First data loaded on '.date("d F Y",$data['dateadded']).'<br />
      @ <a title="Open in new window" target="_blank" href="'.$url.'">'.$url.'</a></p>
      </td></tr></table>
              
      <div style="clear:both"></div>
      </div>
      ';
    $loopid++;
    if ( $swapcolours == 'fff') { $swapcolours = 'ffffef'; } else { $swapcolours = 'fff'; }
    } // end foreach loop
    
    echo '<script type="text/javascript">
    <!--
    //determine num of data   
    document.getElementById("wpeds_numsaveddata_all").innerHTML = \''.($loopid-1).'\';
    document.getElementById("wpeds_numsaveddata_plugins").innerHTML = \''.$numofplugins.'\';
    document.getElementById("wpeds_numsaveddata_themes").innerHTML = \''.$numofthemes.'\';
    
    function wpeds_limitresult(type) {
      if (type == "all") {
        document.getElementById("wpeds_saveddata_jscontrol_text_all").style.borderBottom = "1px solid #888888";
        document.getElementById("wpeds_saveddata_jscontrol_text_plugins").style.borderBottom = "0px";
        document.getElementById("wpeds_saveddata_jscontrol_text_themes").style.borderBottom = "0px";
        for (var i=1;i<'.$loopid.';i++) {
          document.getElementById("wpeds_saveddata_item_"+i).style.display = "block"
        }
      } else {
        document.getElementById("wpeds_saveddata_jscontrol_text_all").style.borderBottom = "0px";
        document.getElementById("wpeds_saveddata_jscontrol_text_plugins").style.borderBottom = "0px";
        document.getElementById("wpeds_saveddata_jscontrol_text_themes").style.borderBottom = "0px";        
        document.getElementById("wpeds_saveddata_jscontrol_text_"+type).style.borderBottom = "1px solid #888888";
        for (var i=1;i<'.$loopid.';i++) {
          var gettype = document.getElementById("wpeds_saveddata_itemtype_"+i).innerHTML;
          if (gettype == type) {
            document.getElementById("wpeds_saveddata_item_"+i).style.display = "block"
          } else {
            document.getElementById("wpeds_saveddata_item_"+i).style.display = "none"
          }
        }
      }
    }
    
    //-->
    </script>';
  }

//var_dump($wpeds_data);

?>
</div>

<!-- -->

<h1 <?php echo $h1style; ?>><a onclick="wpeds_toggle('wpeds_oneblock_addnewdata')">Add New Data</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_addnewdata">
<form style="margin:10px;" method="post" onsubmit="if (document.getElementById('wpeds_syncnew_url').value == '') { document.getElementById('wpeds_syncnew_url').focus(); return false; } else { return true; }">
URL to stats page : <input type="text" name="wpeds_url" id="wpeds_syncnew_url" style="border:1px solid #dddddd;padding:2px" size="70" value="" />
<input type="submit" name="wpeds_syncnew" value="Synchronize" class="button-primary">
</form>

<div class="wpeds_css_notice"><small>Please enter the url to the statistic page. EG: http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/stats/</small></div>
</div>

<!-- -->

<h1 <?php echo $h1style; ?>><a onclick="wpeds_toggle('wpeds_oneblock_customformat')">Custom Output Formats</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_customformat">
<strong><u>User-created Formats</u></strong><br />
<?php
if (empty($wpeds_formats)) {
echo 'None found.';
} else {
//var_dump($wpeds_formats);
  echo '<table class="widefat"><thead><tr><th>Id</th><th>Name</th><th>Format</th><th>Action</th><th>Use this</th></tr></thead>';
  foreach ($wpeds_formats as $formatid => $format) {
  if ($format['format']!='') {
    echo '<tr><td>'.$formatid.'</td><td><form method="post" action="">
    <div id="wpeds_formatdiv_name_'.$formatid.'">'.$format['name'].'</div>
    <div style="display:none" id="wpeds_formatdiv_editname_'.$formatid.'"><input style="border:1px solid #cccccc;padding:2px" type="text" name="wpeds_formatname" value="'.$format['name'].'" /></div>
    </td><td>
    <div id="wpeds_formatdiv_format_'.$formatid.'">'.htmlspecialchars(stripslashes($format['format'])).'</div>
    <div style="display:none" id="wpeds_formatdiv_editformat_'.$formatid.'"><input style="border:1px solid #cccccc;padding:2px" type="text" name="wpeds_formattags" value="'.$format['format'].'" size="40" /><br />
    <input type="submit" name="wpeds_editformat" class="button-primary" value="Save Edit" /></div>
    </td><td width="90">
    <input type="button" name="wpeds_deleteformat" class="button" onclick="wpeds_jsfunc_toggleform(\''.$formatid.'\'); if (this.value == \'Edit\') { this.value = \'Cancel Edit\'; } else { this.value = \'Edit\'; }" value="Edit" /><br />
    <input type="submit" onclick="return confirm(\'Are you sure you want to delete this format of id '.$formatid.' ?\')" name="wpeds_deleteformat" class="button" value="Delete" />
    <input type="hidden" name="wpeds_formatid" value="'.$formatid.'" />
    </form>
    </td><td width="140"><textarea rows="2" cols="20">[downloadstat url="" format="'.$formatid.'"]</textarea></td></tr>';
  } else {//deleted format
    $deletedformats .= '<tr><td>'.$formatid.'</td><td><form method="post" action="">
    <div id="wpeds_formatdiv_name_'.$formatid.'">Deleted</div>
    <div style="display:none" id="wpeds_formatdiv_editname_'.$formatid.'"><input style="border:1px solid #cccccc;padding:2px" type="text" name="wpeds_formatname" value="" /></div></td>
    <td><div id="wpeds_formatdiv_format_'.$formatid.'">Deleted</div>
    <div style="display:none" id="wpeds_formatdiv_editformat_'.$formatid.'"><input style="border:1px solid #cccccc;padding:2px" type="text" name="wpeds_formattags" value="" size="40" /><br />
    <input type="submit" name="wpeds_editformat" class="button-primary" value="Save" /></div></td>
    <td width="90"><input type="button" name="wpeds_editformat" class="button" onclick="wpeds_jsfunc_toggleform(\''.$formatid.'\'); if (this.value == \'Recover\') { this.value = \'Cancel\'; } else { this.value = \'Recover\'; }" value="Recover" /><br /><input type="hidden" name="wpeds_formatid" value="'.$formatid.'" /></form></td>
    <td width="140">-</td>
    </tr>
    ';
  }
  }//end foreach loop
  
  if ($deletedformats!='') { echo $deletedformats; }
  echo '</table>';
}
//var_dump($wpeds_formats);
?>

<br /><br />
<strong><u>Add New Format</u></strong><br />
<?php include( ABSPATH . 'wp-content/plugins/wordpress-extend-download-stat/listoftags.html'); ?>

<form method="post" action="" onsubmit="if (document.getElementById('wpeds_formattags').value == '') { document.getElementById('wpeds_formattags').focus(); return false; } else { return true; }">
<table><tr>
<td width="60">Name</td><td><input type="text" name="wpeds_formatname" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
<td>Format</td><td><input type="text" name="wpeds_formattags" id="wpeds_formattags" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
</table>
<input type="submit" name="wpeds_addformat" value="Add Format" class="button-primary">
</form>


<br /><br />
<strong><u>Default format</u></strong><br />
If invalid format id was found, default format (below) will be used:
<p><code>&lt;a href="{url}">{name}&lt;/a> has been downloaded {total} times in total.</code></p>

</div>


<!-- -->


<!--h1 <?php echo $h1style; ?>><a onclick="wpeds_toggle('wpeds_oneblock_overview')">Overview</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_overview">
You have <strong><?php echo count($wpeds_data); ?></strong> download stats entries.
<?php
  //if (!empty($wpeds_data)) {
  //  
  //}
?>
<br />
You have <strong><?php echo count($wpeds_formats); ?></strong> custom formats.<br />
</div-->


<br /><br />
<hr style="border:0px;height:1px;font-size:1px;margin-bottom:5px;background:#dddddd;color:#dddddd" />
<small style="color:#999999">
<a target="_blank" href="http://zenverse.net/category/wordpress-plugins/">More plugins by me</a> &nbsp; | &nbsp; <a target="_blank" href="http://zenverse.net/category/wpthemes/">Free Wordpress Themes</a> &nbsp; | &nbsp; Thank you for using my plugin.
</small>


<?php
} // end function wpeds_options

############# ADMIN HEAD #############

add_action('admin_head', 'wpeds_admin_head');
function wpeds_admin_head() {
global $zv_wpeds_plugin_dir;
echo '
<!-- start wordpress extend download stat admin_head -->
  <link rel="stylesheet" href="'.$zv_wpeds_plugin_dir.'style.css" type="text/css" />
  <script type="text/javascript">
  document.write(\'<link rel="stylesheet" href="'.$zv_wpeds_plugin_dir.'style_js.css" type="text/css" />\');  
  </script>
  <script type="text/javascript" src="'.$zv_wpeds_plugin_dir.'static.js"></script>
<!-- end wordpress extend download stat admin_head -->
';
}

############# WP FOOTER #############

function wpeds_wpfooter() {
global $zv_wpeds_urltoautosync,$zv_wpeds_plugin_dir;

  if ($zv_wpeds_urltoautosync) {
  $zv_wpeds_urltoautosync = implode($zv_wpeds_urltoautosync,',');
  echo '<!-- start Wordpress Extend Download Stat wp_footer -->
  <script type="text/javascript" src="'.$zv_wpeds_plugin_dir.'loadresync.php?url='.urlencode($zv_wpeds_urltoautosync).'"></script>
  <!-- end Wordpress Extend Download Stat wp_footer -->';
  }
  //var_dump($zv_wpeds_urltoautosync);

}

function wpeds_wphead() {
global $zv_wpeds_plugin_dir,$wpeds_options,$wpeds_plugin_adminhead;
$wpeds_data = get_option('wpeds_data');

  //need auto sync??
  if (!empty($wpeds_options) && isset($wpeds_options['autosynctime']) && is_numeric($wpeds_options['autosynctime'])) {
  if ($wpeds_options['autosynctime'] == 0) { return; }
    $autosynctime = $wpeds_options['autosynctime'];
  } else {
    $autosynctime = 86400;
  }
  
  if (!empty($wpeds_data) && count($wpeds_data)>0) {
    foreach ($wpeds_data as $url => $data) {
      if ((wpeds_return_curr_timestamp() - $data['lastsync']) >= $autosynctime) {
        $toberesync_db[] = $url;
      }
    }
  } else { return; }
  
  if (is_array($toberesync_db) && count($toberesync_db) > 0) {
    $toberesync = implode($toberesync_db,',');
      $extraurl = '';
      if ($wpeds_plugin_adminhead) { $extraurl = '&pluginpage=1'; }
    echo '
    <!-- start Wordpress Extend Download Stat wp_head -->
    <script type="text/javascript" src="'.$zv_wpeds_plugin_dir.'loadresync.php?url='.urlencode($toberesync).$extraurl.'"></script>
    <!-- end Wordpress Extend Download Stat wp_head -->
    ';
  }
  
}

############# ADD MEDIA BUTTON #############

add_action('media_buttons', 'wpeds_add_media_button', 20);
		
function wpeds_add_media_button() {
global $zv_wpeds_plugin_dir;
	echo '<a href="'.$zv_wpeds_plugin_dir.'media.php?tab=add&TB_iframe=true&amp;height=500&amp;width=640" class="thickbox" title="Add Wordpress Extend Download Stat"><img src="'.$zv_wpeds_plugin_dir.'images/media.gif" alt="Add Wordpress Extend Download Stat"></a>';
}