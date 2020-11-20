var ___isArray = function(v) {
	if (typeof v === "object")
	{
		if (typeof v.length == "undefined")
		{
			return false;
		}
		return true;
	}
	return false;
};
__applyToNonArraObject("toArray", function(){
	var v = this.valueOf();
	if (___isArray(v))
	{
		return v;
	}
	return v.toString().trim().split(/,/g);
});