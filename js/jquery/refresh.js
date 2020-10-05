/**
 * Refresh stored selector html elements
 */
jQuery.fn.refresh = function ()
{
	var elems = $(this.selector).toArray();
	this.splice(0, this.length);
	this.push.apply(this, elems);
	return this;
};