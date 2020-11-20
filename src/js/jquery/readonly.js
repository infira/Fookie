//depend:jquery/toggleAttribute
/**
 * Set readonly on or off
 * @param {Bool}  - on or off
 */
jQuery.fn.readonly = function (onOf)
{
	return this.toggleAttribute("readonly", onOf);
};