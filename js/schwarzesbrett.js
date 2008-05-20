function showArtikel(id, link)
{
	$('content_'+id).show();
	$('headline_'+id).hide();
	new Ajax.Request(link, {method: 'post', parameters: {objid: id} });
}

function closeArtikel(id)
{
	$('content_'+id).hide();
	$('headline_'+id).show();
}

function toogleThema(id)
{
	$('list_'+id).toggle();
}