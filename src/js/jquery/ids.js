/**
 * Set global IDS to elements
 * @param {String} name
 * @return attr value
 */
var __globalIDCount = 1;
jQuery.fn.setIDS = function ()
{
	this.each(function ()
	{
		var $me = $(this);
		if (!$me.attr("id"))
		{
			$me.attr("id", "epEl_" + __globalIDCount);
			__globalIDCount++;
		}
	})
	return this;
};
jQuery.fn.getID = function ()
{
	this.setIDS();
	return this.attr("id");
};