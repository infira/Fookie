var JSVars = new function ()
{
	this.get = function (name)
	{
		if (typeof __JSVars[name] != "undefined")
		{
			return __JSVars[name];
		}
		return false;
	}
	
	this.set = function (name, value)
	{
		if (typeof __JSVars[name] != "undefined")
		{
			return __JSVars[name] = value;
		}
		return false;
	}
	
	this.exists = function (name)
	{
		if (typeof __JSVars[name] != "undefined")
		{
			return true;
		}
		return false;
	}
}
