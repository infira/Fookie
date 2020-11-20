/**
 * Remove style from element inline
 * @param {String} style
 */
jQuery.fn.removeInlineStyle = function (style)
{
	var search = new RegExp(style + '[^;]+;?', 'g');
	
	return this.each(function ()
	{
		$(this).attr('style', function (i, style)
		{
			return style.replace(search, '');
		});
	});
};