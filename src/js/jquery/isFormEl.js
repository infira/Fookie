/**
 * Is the element form,select or input
 * @return {Bool}
 */
jQuery.fn.isFormEl = function ()
{
	return this.is("select,input,textarea");
};