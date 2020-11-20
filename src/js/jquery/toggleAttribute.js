/**
 * Toggle DOM element attribute, like disabled,readonly
 * @param {String} name
 * @param {Object} value
 */
jQuery.fn.toggleAttribute = function (name, onOff)
{
	if (onOff == true)
	{
		this.attr(name, name);
	}
	else
	{
		this.removeAttr(name);
	}
	return this;
};