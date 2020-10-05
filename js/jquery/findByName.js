/**
 * Find elements by name
 * @return {Bool}
 */
jQuery.fn.findByName = function (name)
{
	return this.find("[name=" + name + "]");
};