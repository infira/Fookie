/**
 * Tick a checkbox or radio button
 * @param {boolean} check
 * @param {boolean} dontTriggerChange
 */
jQuery.fn.tick = function (check, dontTriggerChange)
{
	this.each(function ()
	{
		var $th = jQuery(this);
		$th.get(0).checked = check;
		if (dontTriggerChange !== true)
		{
			$th.trigger("change");
		}
	})
	return this;
};