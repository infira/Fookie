var Http = new function ()
{
	this.get = function (name)
	{
		if (typeof __Http[name] != "undefined")
		{
			return __Http[name];
		}
		return false;
	}
	
	this.go = function (link)
	{
		window.location = link;
	}
}
