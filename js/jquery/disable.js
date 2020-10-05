/**
 * Add disabled attribute from DOM element
 */
jQuery.fn.disable = function ()
{
	return this.attr("disabled", "disabled");
};
jQuery.fn.enable = function ()
{
	return this.removeAttr("disabled");
};