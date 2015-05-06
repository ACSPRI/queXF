
var aMarquee;

function init()
{
	aMarquee = new Marquee('sampleid', {color: '#99f', opacity: 0.4}); 
	//a.setOnUpdateCallback(finishDrag); 
	Event.observe('rmt_window_0', 'mouseup', submitArea);
	
}



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




function submitArea() {
  var coords = aMarquee.getCoords();
  // coords.leftTop - left top corner
  // coords.rightBottom - right bottom corner
  
  //new Ajax.Request(server_url, {parameters: 'x=' + coords.x1 + '&y=' + coords.y1 + '&width=' + coords.width + '&height=' + coodrs.height, onComplete: onSendCoordsComplete}); 
   va = getUrlVars();
   new Ajax.Updater('imageboxes', 'bandajax.php', {parameters: 'x=' + coords.x1 + '&y=' + coords.y1 + '&w=' + coords.width + '&h=' + coords.height + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });

}

function updateArea(bid,tlx,tly,brx,bry) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'pagelayout.php', {parameters: 'bid=' + bid + '&tlx=' + tlx + '&tly=' + tly + '&brx=' + brx + '&bry=' + bry + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });

}

function hideValueLabels() {
	inputs = document.getElementsByTagName('input');
	for (index = 0; index < inputs.length; ++index) {
		if (inputs[index].name.substr(0,8) == 'boxvalue' || inputs[index].name.substr(0,8) == 'boxlabel')
			inputs[index].style.visibility = 'hidden';
	}
}

function updateVarname(bgid, varname) {
	va = getUrlVars();
	new Ajax.Request('bandajax.php', {parameters: 'bgid=' + bgid + '&varname=' + varname + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get' });

}

function updateVarlabel(bgid, varlabel) {
	va = getUrlVars();
	new Ajax.Request('bandajax.php', {parameters: 'bgid=' + bgid + '&varlabel=' + varlabel + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get' });

}

function updateValue(bid, value) {
	va = getUrlVars();
	new Ajax.Request('bandajax.php', {parameters: 'bid=' + bid + '&value=' + value + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get' });

}

function updateLabel(bid, label) {
	va = getUrlVars();
	new Ajax.Request('bandajax.php', {parameters: 'bid=' + bid + '&label=' + label + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get' });

}

function updateBoxes(bid,btid) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'bandajax.php', {parameters: 'bid=' + bid + '&btid=' + btid + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });

}

function deleteBox(bid) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'bandajax.php', {parameters: 'deletebid=' + bid + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });

}

function deleteInBetween(bid) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'bandajax.php', {parameters: 'deleteinbetween=' + bid + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });
}


function deleteBoxGroup(bid) {
	va = getUrlVars();
	new Ajax.Updater('imageboxes', 'bandajax.php', {parameters: 'deletegroupbid=' + bid + '&pid=' + va['pid'] + '&zoom=' + va['zoom'] + '&qid=' + va['qid'],  method: 'get', evalScripts: 'true'  });

}

window.onload = init;

