/**
 * Add disabled attribute from DOM element
 */
jQuery.fn.enable = function ()
{
	return this.removeAttr("disabled");
};