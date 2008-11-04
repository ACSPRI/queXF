function link(obj,url)
{
	var a = document.getElementById(obj)
	if (a)
	{
		var clone = a.cloneNode(true);
		var pnode = a.parentNode;
		clone.data = url;
		pnode.removeChild(a);
		pnode.appendChild(clone);
	}

}
