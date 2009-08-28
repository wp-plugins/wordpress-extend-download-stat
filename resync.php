<?php
define('DOING_AJAX', true);
define('WP_ADMIN', true);

require_once('../../../wp-load.php');
require_once('functions.php');

if ($_POST['url'] == '') {
  die();
} else {
  $atleastonetrue = false;
  $wpeds_data = get_option('wpeds_data');
  
  $wpeds_urls_toresync = explode(',',$_POST['url']);
  
  foreach ($wpeds_urls_toresync as $wpeds_url) {
  
  if (wpeds_validstaturl($wpeds_url)) {
  
    $olddata = null;
    if (isset($wpeds_data[$wpeds_url])) {
    $olddata = $wpeds_data[$wpeds_url];
    }
    
    $getallstat = wpeds_getstat($wpeds_url,$olddata);
    
    if ($getallstat) {
      $wpeds_data[$wpeds_url] = $getallstat;
      $atleastonetrue = true;
    }
  }
  }//end foreach
  
  if ($atleastonetrue) {
  //update data
  update_option('wpeds_data',$wpeds_data);
  
    if ($_POST['pluginpage'] == '1') {
      $numofresync = count($wpeds_urls_toresync);
      echo '@'.$numofresync;
    }
  }
}



?>