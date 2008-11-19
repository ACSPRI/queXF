var dragsort = ToolMan.dragsort();
var junkdrawer = ToolMan.junkdrawer();

function verticalOnly(item) {
	item.toolManDragGroup.verticalOnly()
}


function saveOrderList(listId) {
    var list = document.getElementById(listId);
    var items = list.getElementsByTagName('li');
    var ids = '';
    var hiddeninput = document.getElementById('list'); // the input field storing the order

    for (var i = 0; i < items.length; i++)  {
      if (i > 0) ids += '|';
      var id = items[i].getAttribute("title");  
      ids += id;
    }

    hiddeninput.value = ids;

    return true;
}   


window.onload = function()
{
	dragsort.makeListSortable(document.getElementById("phoneticlong"),verticalOnly);
}
