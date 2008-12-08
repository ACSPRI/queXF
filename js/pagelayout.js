// Read a page's GET URL variables and return them as an associative array.
// From: http://snipplr.com/view/799/get-url-variables/
function getUrlVars()
{
  var vars = [], hash;
  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

  for(var i = 0; i < hashes.length; i++)
  {
    hash = hashes[i].split('=');
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
  }
  return vars;
}


function updateArea(bid,tlx,tly,brx,bry) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'pagelayout.php', {parameters: 'bid=' + bid + '&tlx=' + tlx + '&tly=' + tly + '&brx=' + brx + '&bry=' + bry + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'false'  });

}
