function showArtikel(id, typ)
{
	if(!typ) typ = '';
	if($('content'+typ+'_'+id).down() === null){
		new Ajax.Request(STUDIP.PLUGIN_URL + 'ajaxdispatch?objid='+id, {
	    	method: 'post',
	    	onSuccess: function(transport) {
				$('content'+typ+'_'+id).update(transport.responseText);
				$('content'+typ+'_'+id).show();
				$('headline'+typ+'_'+id).hide();
			},
	       	onFailure: function(t) {alert('Error ' + t.status + ' -- ' + t.statusText); },
	   		on404: function(t) {alert('Error 404: location "' + t.statusText + '" was not found.'); }
		});
	} else {
		$('content'+typ+'_'+id).show();
		$('headline'+typ+'_'+id).hide();
	}
}

function closeArtikel(e)
{
	var content = e.up();
	var headline = content.up().down();
	var id = content.id.split('_')[1];
	content.hide();
	headline.show();
	$('indikator_'+id).src = STUDIP.ABSOLUTE_URI_STUDIP+'assets/images/forumgrau.gif';
}

function toogleThema(id)
{
	if($('list_'+id).down() === null){
		new Ajax.Request(STUDIP.PLUGIN_URL + 'ajaxdispatch?thema_id='+id, {
	    	method: 'post',
	    	onSuccess: function(transport) {
				$('list_'+id).update(transport.responseText);
				$('list_'+id).toggle();
			},
	       	onFailure: function(t) {alert('Error ' + t.status + ' -- ' + t.statusText); },
	   		on404: function(t) {alert('Error 404: location "' + t.statusText + '" was not found.'); }
		});
	} else {
		$('list_'+id).toggle();
	}
}