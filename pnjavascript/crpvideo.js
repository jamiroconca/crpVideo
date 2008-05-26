/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpcalendar Support and documentation
 * @version $Id: $ 
 * @author Daniele Conca <conca dot daniele at gmail dot com>
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