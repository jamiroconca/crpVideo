/**
 * crpVideo
 *
 * @copyright (c) 2007-2008, Daniele Conca
 * @link http://code.zikula.org/projects/crpvideo Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpVideo
 */

function crpVideoAdminViewInit()
{
	var real = $$('span.crp-status-real');
	
	real.each(
		function(node) 
		{ 
			node.addClassName('pn-hide');
		}
	)
	
	var fake = $$('span.crp-status-fake');
	
	fake.each(
		function(node) 
		{ 
			node.removeClassName('pn-hide');
		}
	)
}

function togglestatus(eventid,status)
{
    var pars = "module=crpVideo&func=toggleStatus&videoid=" + eventid
    			+"&status=" + status;
    var myAjax = new Ajax.Request(
        "ajax.php", 
        {
            method: 'get', 
            parameters: pars, 
            onComplete: togglestatus_response
        });
}

function togglestatus_response(req)
{
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    
    var jsonArray = pndejsonize(req.responseText);

    $('videostatus_fake_A_' + jsonArray.videoid).toggle();
    $('videostatus_fake_P_' + jsonArray.videoid).toggle();
}

function crpVideoConfigInit(gd_version)
{
	if (gd_version < 2)
	{ 
		$('crpvideo_use_gd').parentNode.remove();
		$('crpvideo_use_browser').removeClassName('pn-hide')
	}
}

function crpVideoContentLoad(){
	Event.observe('videoid_category', 'change', function(){
		category_video();
	}, false);
}

// 
function category_video(){
	var pars = "module=crpVideo&func=getCategorizedVideo&" +
	'&category=' +
	$F('videoid_category');
	
	var myAjax = new Ajax.Request("ajax.php", {
		method: 'get',
		parameters: pars,
		onComplete: category_video_response
	});
}

function category_video_response(req){
	if (req.status != 200) {
		pnshowajaxerror(req.responseText);
		showinfo();
		return;
	}
	
	var videoSelect = $('contentVideo');
	
	var i;
	for (i = videoSelect.length - 1; i >= 0; i--) {
		videoSelect.remove(i);
	}
	
	var jsonArray = pndejsonize(req.responseText);
	
	for (i in jsonArray) {
		if (isNumeric(i)) {
			var optNew = document.createElement('option');
			optNew.text = jsonArray[i].name;
			optNew.value = jsonArray[i].id;
			try {
				videoSelect.add(optNew, null);
			} 
			catch (ex) {
				videoSelect.add(optNew);
			}
		}
	}
}

// key verification
function isNumeric(strString)//  check for valid numeric strings
{
	var strValidChars = "0123456789.-";
	var strChar;
	var blnResult = true;
	
	if (strString.length == 0) 
		return false;
	
	//  test strString consists of valid characters listed above
	for (k = 0; k < strString.length && blnResult == true; k++) {
		strChar = strString.charAt(k);
		if (strValidChars.indexOf(strChar) == -1) {
			blnResult = false;
		}
	}
	return blnResult;
}