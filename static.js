function wpeds_jsfunc_toggleform(formatid) {
var getelemx = document.getElementById("wpeds_formatdiv_editname_"+formatid);
  if (getelemx.style.display != "block") {
    getelemx.style.display = "block";
    document.getElementById("wpeds_formatdiv_editformat_"+formatid).style.display = "block";
    document.getElementById("wpeds_formatdiv_name_"+formatid).style.display = "none";
    document.getElementById("wpeds_formatdiv_format_"+formatid).style.display = "none";
  } else {
    getelemx.style.display = "none";
    document.getElementById("wpeds_formatdiv_editformat_"+formatid).style.display = "none";
    document.getElementById("wpeds_formatdiv_name_"+formatid).style.display = "block";
    document.getElementById("wpeds_formatdiv_format_"+formatid).style.display = "block";
  }
}
  

function wpeds_toggle(elemid,hidethistoo) {
var getelemx = document.getElementById(elemid);
  if (getelemx.style.display != "block") {
    getelemx.style.display = "block";
      if (hidethistoo != null) {
        document.getElementById(hidethistoo).style.display = "none";
      }
  } else {
    getelemx.style.display = "none";
  }
}


function wpeds_showhide_lastdatadiv(loopid) {
var getelem1 = document.getElementById("wpeds_lastdatadiv1_"+loopid);
var getelem2 = document.getElementById("wpeds_lastdatadiv2_"+loopid);
var getelem3 = document.getElementById("wpeds_lastdatadiv3_"+loopid);
var getelem4 = document.getElementById("wpeds_lastdatadiv4_"+loopid);
var getelem5 = document.getElementById("wpeds_lastdatadiv5_"+loopid);
  if (getelem1.style.display != "block") {
    getelem1.style.display = "block";
    getelem2.style.display = "block";
    getelem3.style.display = "block";
    getelem4.style.display = "block";
    getelem5.style.display = "block";
  } else {
    getelem1.style.display = "none";
    getelem2.style.display = "none";
    getelem3.style.display = "none";
    getelem4.style.display = "none";
    getelem5.style.display = "none";
  }
}