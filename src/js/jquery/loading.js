jQuery.fn.appedOverlay = function ()
{
	var $appendTo = false;
	if (this.attr("data-overlay-selector"))
	{
		var $overlay = $(this.attr("data-overlay-selector"));
	}
	if (this.is(".overlay"))
	{
		var $overlay = this;
	}
	else
	{
		var $appendTo = this;
	}
	$(this).addClass("relativeImportant");
	if ($appendTo !== false)
	{
		if ($appendTo.find(">.overlay").length <= 0)
		{
			$appendTo.append('<div data-overlay-is-active="0" class="overlay" />');
		}
		var $overlay = $appendTo.find(">.overlay");
	}
	if (typeof $overlay.attr("data-overlay-is-active") == "undefined")
	{
		if ($overlay.attr("class") == "overlay")
		{
			$overlay.attr("data-overlay-is-active", "0");
		}
		else
		{
			$overlay.attr("data-overlay-is-active", "1");
		}
	}
	if (!$overlay.attr("data-overlay-is-active"))
	{
		$overlay.attr("data-overlay-is-active", ($overlay.attr("class") == "overlay") ? 0 : 1);
	}
	return $overlay;
};

/**
 * Set over lay
 */
jQuery.fn.__showOverlay = function (overlayClass, animate)
{
	var $overlay;
	this.each(function ()
	{
		$overlay = $(this).appedOverlay();
		$overlay.attr("class", "overlay " + overlayClass);
		if ($overlay.attr("data-overlay-is-active") != 1)
		{
			if (animate)
			{
				$overlay.hide();
			}
			var speed = 0;
			if (animate)
			{
				speed = "fast";
			}
			$overlay.stop().fadeTo(0, 0).show().fadeTo(speed, 1, function ()
			{
				$(this).show();
			});
			$overlay.attr("data-overlay-is-active", 1);
		}
	});
	return this;
};
/**
 * Remvoe overlay
 * @param {Object} animate
 */
jQuery.fn.__removeOverlay = function (animate)
{
	var $overlay;
	this.each(function ()
	{
		$overlay = $(this).appedOverlay();
		var speed = 0;
		if ($overlay.attr("data-overlay-is-active") != 0)
		{
			if (animate)
			{
				speed = "fast";
			}
			$overlay.stop().fadeTo(0, 1).show().fadeTo("fast", 0, function ()
			{
				$(this).attr("class", "overlay").hide();
			});
		}
		$overlay.attr("data-overlay-is-active", 0);
	});
	return this;
};

jQuery.fn.loading = function (showOrHide, animate)
{
	if (animate == undefined)
	{
		animate = true;
	}
	animate = (animate) ? true : false;
	if (showOrHide == true)
	{
		this.addClass("onLoading");
		this.__showOverlay("loading", animate);
	}
	else
	{
		this.removeClass("onLoading");
		this.__removeOverlay(animate);
	}
	return this;
};

/**
 * Disable visiby element
 */
jQuery.fn.lock = function (animate)
{
	this.__showOverlay("locked", animate);
	return this;
};

/**
 * Disable visiby element
 */
jQuery.fn.unlock = function (animate)
{
	this.__removeOverlay(animate);
	return this;
};
/**
 * Disable visiby element
 */
jQuery.fn.checked = function (animate)
{
	this.__showOverlay("checked", animate);
	return this;
};

/**
 * Disable visiby element
 */
jQuery.fn.unchecked = function (animate)
{
	this.__removeOverlay()
	return this;
};