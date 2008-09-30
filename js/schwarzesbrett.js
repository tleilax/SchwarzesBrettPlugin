function showArtikel(id, link)
{
	$('content_'+id).show();
	$('headline_'+id).hide();
	new Ajax.Request(link, {method: 'post', parameters: {objid: id} });
}

function closeArtikel(id, url)
{
	$('content_'+id).hide();
	$('headline_'+id).show();
	$('indikator_'+id).src = 'assets/images/forumgrau.gif';
}

function toogleThema(id)
{
	$('list_'+id).toggle();
}