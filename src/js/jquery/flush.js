//depend:jquery/setVal
/**
 * Flush form element
 */
jQuery.fn.flush = function ()
{
	if (this.is("select"))
	{
		this.html("");
	}
	if (this.hasClass("custom_inputs"))
	{
		this.iCheck('uncheck');
	}
	this.setVal("");
	return this;
};