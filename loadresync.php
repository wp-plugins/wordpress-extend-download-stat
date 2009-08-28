<?php
Header("content-type: application/x-javascript");

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~

require_once('../../../wp-load.php');
require_once('functions.php');

// Pre 2.6 compatibility (BY Stephen Rider)
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	if ( defined( 'WP_SITEURL' ) ) define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
	else define( 'WP_CONTENT_URL', get_option( 'url' ) . '/wp-content' );
}

$zv_wpeds_plugin_dir = WP_CONTENT_URL.'/plugins/wordpress-extend-download-stat/';

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if ($_GET['url'] == '') {
die();
}

?>

function zv_wpeds_ajax() {
//alert('runned');
	var ajaxRequest;
	
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				//alert("Your browser broke!");
				return false;
			}
		}
	}
	
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
		//alert(ajaxRequest.responseText);
		<?php
      if ($_GET['pluginpage'] == '1') {
        echo '
        if (ajaxRequest.responseText.charAt(0) == "@") {
          try {
            document.getElementById("wpeds_resync_status_div").style.display = "block";
            document.getElementById("wpeds_resync_status_div").innerHTML = ajaxRequest.responseText.substr(1)+" items has been successfully resynchronized.";
          } catch (e) {
            //alert(e);
          }
        }
        ';
      }
    ?>
	}
	}
	
  var params = "url=<?php echo $_GET['url']; ?>";
  
  <?php
    if ($_GET['pluginpage'] == '1') {
    echo 'params = params+"&pluginpage=1";';
    }
  ?>
  
	ajaxRequest.open("POST", "<?php echo $zv_wpeds_plugin_dir; ?>resync.php", true);
	
	//Send the proper header information along with the request
  ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  ajaxRequest.setRequestHeader("Content-length", params.length);
  ajaxRequest.setRequestHeader("Connection", "close");
	
  ajaxRequest.send(params);
}

function zv_wpeds_addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = func;
    } else {
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}

zv_wpeds_addLoadEvent(zv_wpeds_ajax);