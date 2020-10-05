/**
 * Scroll element
 * @param offset
 * @param callback
 * @param $scrollable - if not defined, body will be used
 * @returns {jQuery}
 */
jQuery.fn.scrollTo = function (offset, callback, $scrollable)
{
	if (!offset)
	{
		offset = 0;
	}
	if ($scrollable)
	{
		$scrollable = $($scrollable);
	}
	else
	{
		$scrollable = $('html, body');
	}
	var $th = $(this);
	var options = {
		easing: ($th.data("scroll-easing")) ? $th.data("scroll-easing") : "linear",
		done: function ()
		{
			if (typeof callback == "function")
			{
				callback();
			}
		}
	};
	var animation = {
		scrollTop: $th.offset().top + offset
	};
	$scrollable.animate(animation, options);
	return this;
};
jQuery.fn.scrollToElement = function (scrollToSelector, offset)
{
	var $scrollable = $(this);
	var $scrollableTop = $scrollable.offset().top;
	var $scrollableBottom = $scrollableTop + $scrollable.height();
	var $scrollTo = $(scrollToSelector);
	var $scrollToTop = $scrollTo.offset().top;
	var $scrollToBottom = $scrollToTop + $scrollTo.height();
	
	if ($scrollToTop > $scrollableTop && $scrollToBottom < $scrollableBottom)
	{
		// in view so don't do anything
		return;
	}
	var newScrollTop;
	if ($scrollToTop < $scrollableTop)
	{
		newScrollTop = {scrollTop: $scrollable.scrollTop() - $scrollableTop + $scrollToTop};
	}
	else
	{
		newScrollTop = {scrollTop: $scrollToBottom - $scrollableBottom + $scrollable.scrollTop()};
	}
	if (typeof offset != "undefined")
	{
		newScrollTop.scrollTop += offset;
	}
	$scrollable.animate(newScrollTop);
	return this;
};