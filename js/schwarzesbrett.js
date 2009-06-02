function showArtikel(id, link, typ)
{
	$('content'+typ+'_'+id).show();
	$('headline'+typ+'_'+id).hide();
	new Ajax.Request(link, {method: 'post', parameters: {objid: id} });
}

function closeArtikel(id, typ)
{
	$('content'+typ+'_'+id).hide();
	$('headline'+typ+'_'+id).show();
	$('indikator_'+id).src = STUDIP.ABSOLUTE_URI_STUDIP+'assets/images/forumgrau.gif';
}

function toogleThema(id)
{
	$('list_'+id).toggle();
}