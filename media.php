<?php
define('WP_ADMIN', true);

require_once('../../../wp-load.php');
require_once('functions.php');

// Pre 2.6 compatibility (BY Stephen Rider)
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	if ( defined( 'WP_SITEURL' ) ) define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
	else define( 'WP_CONTENT_URL', get_option( 'url' ) . '/wp-content' );
}
 
// REPLACE ADMIN URL
if (function_exists('admin_url')) {
	wp_admin_css_color('classic', __('Blue'), admin_url("css/colors-classic.css"), array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), admin_url("css/colors-fresh.css"), array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
} else {
	wp_admin_css_color('classic', __('Blue'), get_bloginfo('wpurl').'/wp-admin/css/colors-classic.css', array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), get_bloginfo('wpurl').'/wp-admin/css/colors-fresh.css', array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}

wp_enqueue_script( 'common' );
wp_enqueue_script( 'jquery-color' );
wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
wp_enqueue_style( 'media' );

$zv_wpeds_plugin_dir = WP_CONTENT_URL.'/plugins/wordpress-extend-download-stat/';
$zv_wpeds_siteurl = get_option('siteurl');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; Wordpres Extend Download Stat &#8212; <?php _e('WordPress'); ?></title>
	
<script type="text/javascript" src="<?php echo $zv_wpeds_plugin_dir; ?>static.js"></script>

<?php
do_action('admin_print_styles');
do_action('admin_print_scripts');
?>

<link rel="stylesheet" href="<?php echo $zv_wpeds_plugin_dir; ?>style.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $zv_wpeds_plugin_dir; ?>style_js.css" type="text/css" />

</head>


<?php
$wpeds_data = get_option('wpeds_data');
$wpeds_formats = get_option('wpeds_formats');
$format_select_string = '
<option value="">[Use default format]</option>
<optgroup label="User-created Formats">
';
if (!empty($wpeds_formats) && $wpeds_formats) {
  foreach ($wpeds_formats as $formatid => $format) {
    if ($format['format'] != '') {
    $format_select_string .= '<option value="'.$formatid.'">'.$format['name'].'</option>';
    }
  }
}
$format_select_string .= '</optgroup>
<optgroup label="Other Actions">
<option value="[new]">[Create a new format]</option>
<option value="[single]">[I want to display a single info only.]</option>
</optgroup>';
?>


<body style="padding:10px">


<?php
if (isset($_POST['wpeds_insert'])) {
$wpeds_error = false;
$wpeds_addhtml = '';

  switch ($_POST['wpeds_inserttype']) {
  case 'new':
    if ($_POST['wpeds_url'] != '') {
      $_POST['wpeds_url'] = wpeds_formaturl($_POST['wpeds_url']);
      if (isset($wpeds_data[$_POST['wpeds_url']])) {//data already exist, pass to resync
        $_POST['wpeds_inserttype'] = 'existing';
      } else {
        if (!wpeds_validstaturl($_POST['wpeds_url'])) {
          $wpeds_error = true;
          $wpeds_errormsg = 'Error. The stats URL is invalid.';
        } else {
        
          // manage data
          $getallstat = wpeds_getstat($_POST['wpeds_url']);
          $wpeds_data[$_POST['wpeds_url']] = $getallstat;
            if ($getallstat) {
            //update data
            update_option('wpeds_data',$wpeds_data);
            $wpeds_addhtml .= '[downloadstat url="'.$_POST['wpeds_url'].'"';
          } else {
            $wpeds_error = true;
            echo 'Error. Invalid data retrieved from the URL.';
          }
          
          // manage format
          if ($_POST['wpeds_format']!='') {
          if ($_POST['wpeds_format']=='[new]') {
            if ($_POST['wpeds_formattags'] == '') {
              $wpeds_error = true;
              $wpeds_errormsg = 'Error. The format cannot be empty.';
            } else {//format tags not empty
              if (empty($wpeds_formats)) {
                $wpeds_formats[1] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
                $formatidused = 1;
              } else {
                ksort($wpeds_formats);
                $allkeys = array_keys($wpeds_formats);
                $countkeys = count($allkeys);
                $getlastkey = $allkeys[($countkeys-1)];
                $wpeds_formats[($getlastkey+1)] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
                $formatidused = $getlastkey+1;
              }
                //update data
                update_option('wpeds_formats',$wpeds_formats);
                $wpeds_addhtml .= ' format="'.$formatidused.'"';
            }
            
          } elseif ($_POST['wpeds_format']=='[single]') {
            $wpeds_addhtml .= ' get="'.$_POST['wpeds_single'].'"';
          } else {
            if (isset($wpeds_formats[$_POST['wpeds_format']])) {
              $wpeds_addhtml .= ' format="'.$_POST['wpeds_format'].'"';
            }
          }
        }
        
        // manage autop
        if (!isset($_POST['wpeds_autop'])) {
          $wpeds_addhtml .= ' autop="false"';
        }
        
        // output
        if (!$wpeds_error) {
        echo '<script type="text/javascript">
					/* <![CDATA[ */
					var win = window.dialogArguments || opener || parent || top;
					win.send_to_editor(\''.$wpeds_addhtml.']\');
					/* ]]> */
				</script>';
				exit;
				}
          
        }
      }
    } else {
      $wpeds_error = true;
      $wpeds_errormsg = 'Error. The URL cannot be empty.';
    }
  break;
  case 'existing':
    if ($_POST['wpeds_url'] != '') {
      if (isset($wpeds_data[$_POST['wpeds_url']])) {
        $wpeds_addhtml .= '[downloadstat url="'.$_POST['wpeds_url'].'"';
        
        if ($_POST['wpeds_format']!='') {
          if ($_POST['wpeds_format']=='[new]') {
            if ($_POST['wpeds_formattags'] == '') {
              $wpeds_error = true;
              $wpeds_errormsg = 'Error. The format cannot be empty.';
            } else {//format tags not empty
              if (empty($wpeds_formats)) {
                $wpeds_formats[1] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
                $formatidused = 1;
              } else {
                ksort($wpeds_formats);
                $allkeys = array_keys($wpeds_formats);
                $countkeys = count($allkeys);
                $getlastkey = $allkeys[($countkeys-1)];
                $wpeds_formats[($getlastkey+1)] = array('name'=>$_POST['wpeds_formatname'],'format'=>$_POST['wpeds_formattags']);
                $formatidused = $getlastkey+1;
              }
                //update data
                update_option('wpeds_formats',$wpeds_formats);
                $wpeds_addhtml .= ' format="'.$formatidused.'"';
            }
            
          } elseif ($_POST['wpeds_format']=='[single]') {
            $wpeds_addhtml .= ' get="'.$_POST['wpeds_single'].'"';
          } else {
            if (isset($wpeds_formats[$_POST['wpeds_format']])) {
              $wpeds_addhtml .= ' format="'.$_POST['wpeds_format'].'"';
            }
          }
        }
        
        if (!isset($_POST['wpeds_autop'])) {
          $wpeds_addhtml .= ' autop="false"';
        }
        
        if (isset($_POST['wpeds_resyncnow'])) {
          $olddata = null;
          if (isset($wpeds_data[$_POST['wpeds_url']])) {
            $olddata = $wpeds_data[$_POST['wpeds_url']];
          }
          
          $getallstat = wpeds_getstat($_POST['wpeds_url'],$olddata);
          $wpeds_data[$_POST['wpeds_url']] = $getallstat;
          
          $nochange = false;    
          if ($olddata) {
            if ($olddata['today'].'/'.$olddata['yesterday'].'/'.$olddata['lastweek'].'/'.$olddata['total'] == $getallstat['today'].'/'.$getallstat['yesterday'].'/'.$getallstat['lastweek'].'/'.$getallstat['total']) {
              $nochange = true;
            }
          }
          
          if (!$nochange) {
            //update data
            update_option('wpeds_data',$wpeds_data);
          }
        }
        
        if (!$wpeds_error) {
        echo '<script type="text/javascript">
					/* <![CDATA[ */
					var win = window.dialogArguments || opener || parent || top;
					win.send_to_editor(\''.$wpeds_addhtml.']\');
					/* ]]> */
				</script>';
				exit;
				}
        
      } else {
        $wpeds_error = true;
        $wpeds_errormsg = 'Error. The data cannot be found.';
      }
    } else {
      $wpeds_error = true;
      $wpeds_errormsg = 'Error. The "Name" is empty.';
    }
    
  break;
  default:
    $wpeds_error = true;
    $wpeds_errormsg = 'Error. Please submit the form properly.';
  break;
  }

}


if (!isset($_POST['wpeds_insert']) || $wpeds_error) {

if ($wpeds_errormsg != '') {
 echo '<div class="wpeds_css_notice">'.$wpeds_errormsg.'</div>';
}

?>

<p>Please choose one from below:</p>

<h1 style="background-image:url(<?php echo $zv_wpeds_plugin_dir; ?>images/titleimg.jpg)" class="wpeds_css_optionh1"><a onclick="wpeds_toggle('wpeds_oneblock_addnew','wpeds_oneblock_existing')">Add New Data</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_addnew">
<form method="post" action="">

<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">URL to stats page</td><td>
<input type="text" name="wpeds_url" value="" style="border:1px solid #cccccc;padding:2px" size="60" /><br />
<small style="color:#888888">Eg: http://wordpress.org/extend/plugins/wordpress-theme-demo-bar/stats/</small>
</td></tr></table>
</div>

<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Output Format</td><td>
<select name="wpeds_format" onchange="formatonchange(this.options[selectedIndex].value,'2')">
<?php echo $format_select_string; ?></select>
</td></tr></table>
</div>


<div style="display:none" class="wpeds_css_optionblock" id="singlecountdiv2">
<table><tr><td width="100" style="font-weight:bold">Display Single Info</td><td>
<select name="wpeds_single">
<optgroup label="Item Info">
<option value="name">Name</option>
<option value="url">URL to download latest version</option>
<option value="type">Type (Wordpress Theme or Wordpress Plugin)</option>
<option value="version">Latest version number</option>
<option value="lastupdate">Date of last update of the plugin/theme files</option>
</optgroup>
<optgroup label="Item Download Count">
<option value="today">Today</option>
<option value="yesterday">Yesterday</option>
<option value="lastweek">Last Week</option>
<option value="total">downloads</option>
</optgroup>
<optgroup label="Misc.">
<option value="lastsync">Date of last synchronize</option>
<option value="freshness">Time difference between last synchronize and now (freshness)</option>
<option value="dateadded">Date when the data was first loaded</option>
</optgroup>
</select><br />
<small>Please note that only numbers will be displayed.</small>
</td></tr></table>
</div>


<div style="display:none" class="wpeds_css_optionblock" id="addnewformatdiv2">
<table><tr><td width="100" style="font-weight:bold">Add New Format</td><td>
  <table><tr>
  <td width="60">Name</td><td><input type="text" name="wpeds_formatname" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
  <td>Format</td><td><input type="text" name="wpeds_formattags" id="wpeds_formattags" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
  </table>
<small>You might need the <a href="<?php echo $zv_wpeds_plugin_dir; ?>listoftags.html" target="_blank">list of tags</a>.</small>
</td></tr></table>
</div>

<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Auto P?</td><td>
<input type="checkbox" name="wpeds_autop" checked="checked" value="1" /><br />
<small>Tick the checkbox if you want to wrap the content with HTML paragraph &lt;p> tag.</small>
</td></tr></table>
</div>

<input type="hidden" name="wpeds_inserttype" value="new">
<p><input type="submit" class="button-primary" name="wpeds_insert" value="Insert into post" />
<br />
<small>* It might takes a few seconds to synchronize the data</small></p>
</form>

</div>



<h1 style="background-image:url(<?php echo $zv_wpeds_plugin_dir; ?>images/titleimg.jpg);" class="wpeds_css_optionh1"><a onclick="wpeds_toggle('wpeds_oneblock_existing','wpeds_oneblock_addnew')">Use Existing Data (stored in your database)</a></h1>
<div class="wpeds_css_oneblock" id="wpeds_oneblock_existing">
<form method="post" action="">

<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Name</td><td>
<?php
$nosaveddata = false;
if (!empty($wpeds_data) && $wpeds_data) {
echo '<select name="wpeds_url" onchange="if (this.options[selectedIndex].value != \'\') { document.getElementById(\'itemstaturl\').innerHTML=\'@ \'+this.options[selectedIndex].value; } else { document.getElementById(\'itemstaturl\').innerHTML=\'\' }"><option value="">Select an item</option>';

  foreach ($wpeds_data as $url => $data) {
    if (strtolower($data['type']) == 'wordpress plugin') {
    $wpeds_select_plugins[] = $url;
    } else {
    $wpeds_select_themes[] = $url;
    }
  }
  
  if (is_array($wpeds_select_plugins) && count($wpeds_select_plugins) > 0) {
  echo '<optgroup label="Wordpress Plugins">';
  foreach ($wpeds_select_plugins as $url) {
  echo '<option value="'.$url.'">'.$wpeds_data[$url]['name'].'</option>';
  }
  echo '</optgroup>';
  }
  
  if (is_array($wpeds_select_themes) && count($wpeds_select_themes) > 0) {
  echo '<optgroup label="Wordpress Themes">';
  foreach ($wpeds_select_themes as $url) {
  echo '<option value="'.$url.'">'.$wpeds_data[$url]['name'].'</option>';
  }
  echo '</optgroup>';
  }
  
echo '</select>';
} else {
echo 'No data exist. Click "Add New Data" at above.';
$nosaveddata = true;
}
?>
<div style="font-size:10px;" id="itemstaturl"></div>
</td></tr></table>
</div>

<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Output Format</td><td>
<select name="wpeds_format" onchange="formatonchange(this.options[selectedIndex].value)">
<?php echo $format_select_string; ?>
</select>

<script type="text/javascript">
  function formatonchange(value,extradivid) {
  if (extradivid == '' || !extradivid || extradivid == null) {
  extradivid = '';
  }
  
    if (value == '') {
     hideunuseddivs(extradivid);
     return;
    } else {
      if (value == '[new]') {
        hideunuseddivs(extradivid);
        document.getElementById("addnewformatdiv"+extradivid).style.display = 'block'
      } else if (value == '[single]') {
        hideunuseddivs(extradivid);
        document.getElementById("singlecountdiv"+extradivid).style.display = 'block'
      } else {
        hideunuseddivs(extradivid);
        return;
      }
    }
  }
  
function hideunuseddivs(extradivid) {
  if (extradivid == '' || !extradivid || extradivid == null) {
  extradivid = '';
  }
document.getElementById("singlecountdiv"+extradivid).style.display = 'none'
document.getElementById("addnewformatdiv"+extradivid).style.display = 'none'
}

</script>
</td></tr></table>
</div>


<div style="display:none" class="wpeds_css_optionblock" id="singlecountdiv">
<table><tr><td width="100" style="font-weight:bold">Display Single Info</td><td>
<select name="wpeds_single">
<optgroup label="Item Info">
<option value="name">Name</option>
<option value="url">URL to download latest version</option>
<option value="type">Type (Wordpress Theme or Wordpress Plugin)</option>
<option value="version">Latest version number</option>
<option value="lastupdate">Date of last update of the plugin/theme files</option>
</optgroup>
<optgroup label="Item Download Count">
<option value="today">Today</option>
<option value="yesterday">Yesterday</option>
<option value="lastweek">Last Week</option>
<option value="total">downloads</option>
</optgroup>
<optgroup label="Misc.">
<option value="lastsync">Date of last synchronize</option>
<option value="freshness">Time difference between last synchronize and now (freshness)</option>
<option value="dateadded">Date when the data was first loaded</option>
</optgroup>
</select><br />
<small>Please note that only numbers will be displayed.</small>
</td></tr></table>
</div>


<div style="display:none" class="wpeds_css_optionblock" id="addnewformatdiv">
<table><tr><td width="100" style="font-weight:bold">Add New Format</td><td>
  <table><tr>
  <td width="60">Name</td><td><input type="text" name="wpeds_formatname" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
  <td>Format</td><td><input type="text" name="wpeds_formattags" id="wpeds_formattags" style="border:1px solid #cccccc;padding:2px" size="60" value="" /></td></tr>
  </table>
<small>You might need the <a href="<?php echo $zv_wpeds_plugin_dir; ?>listoftags.html" target="_blank">list of tags</a>.</small>
</td></tr></table>
</div>


<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Auto P?</td><td>
<input type="checkbox" name="wpeds_autop" checked="checked" value="1" /><br />
<small>Tick the checkbox if you want to wrap the content with HTML paragraph &lt;p> tag.</small>
</td></tr></table>
</div>


<div class="wpeds_css_optionblock">
<table><tr><td width="100" style="font-weight:bold">Resync Now?</td><td>
<input type="checkbox" name="wpeds_resyncnow" value="1" /><br />
<small>Tick the checkbox if you want to resync the data after you press "Insert Into Post".</small>
</td></tr></table>
</div>


<p><input type="submit" <?php if ($nosaveddata) { echo 'disabled="disabled"'; } ?> class="button-primary" name="wpeds_insert" value="Insert into post" /></p>
<input type="hidden" name="wpeds_inserttype" value="existing">
</form>
</div>

<?php
}
?>

<br /><br />
<hr style="border:0px;height:1px;font-size:1px;background:#cccccc;color:#cccccc" />
<small>
&raquo; <a target="_blank" href="<?php echo $zv_wpeds_siteurl; ?>/wp-admin/options-general.php?page=wordpress-extend-download-stat/wordpress-extend-download-stat.php">Visit the plugin option page</a>
</small>
</body></html>