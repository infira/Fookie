/**
 * Find elements by name
 * @return {Bool}
 */
jQuery.fn.debug = function (name)
{
	return debug($(this));
};
jQuery.fn.reverse       = [].reverse;
jQuery.fn.slideUpRemove = function (callback, speed)
{
	if (speed == undefined)
	{
		speed = "400"
	}
	this.slideUp(speed, function ()
	{
		$(this).addClass("toBeRemoved");
		if (Object.prototype.toString.call(callback) === '[object Function]')
		{
			callback();
		}
		$(this).remove();
	})
	return this;
};
jQuery.fn.fadeOutRemove = function ()
{
	this.fadeOut(function ()
	{
		$(this).remove();
	})
	return this;
};
jQuery.fn.showNotice    = function (msg)
{
	this.parent().find(".buttonOkText").remove();
	if (!msg)
	{
		msg = this.data("ok-msg");
	}
	$('<span class="buttonOkText">' + msg + '</span>').insertAfter($(this)).delay(1000).fadeOutRemove();
	return this;
};
/**
 * Get input or jquery Element error
 */
jQuery.fn.getError = function ()
{
	return this.data("formError") || this.data("formError-ajax");
};
/**
 * Set input or jquery Element error
 */
jQuery.fn.setError = function (errorMessage, isErorFromAjax)
{
	if (isErorFromAjax)
	{
		this.data("formError-ajax", errorMessage);
	}
	else
	{
		this.data("formError", errorMessage);
	}
	return this;
};

/**
 * See is element have any error flags
 * @param checkOnlylocalErrors
 * @returns {boolean}
 */
jQuery.fn.isError = function (checkOnlylocalErrors)
{
	if (checkOnlylocalErrors == true)
	{
		if (this.data("formError"))
		{
			return true;
		}
		return false;
	}
	if (this.getError())
	{
		return true;
	}
	return false;
};

/**
 * Remove error flags
 */
jQuery.fn.delError = function ()
{
	this.data("formError", false);
	this.data("formError-ajax", false);
	return this;
};

var getBodyActualWidth = function ()
{
	jQuery("body").css("overflow", "hidden");
	var ww = $(window).width();
	jQuery("body").css("overflow", "");
	return ww;
};

var ObjectProps = function (items)
{
	this.items = items;
};
jQuery.extend(ObjectProps.prototype, {
	
	exists: function (name)
	{
		if (typeof this.items[name] == "undefined")
		{
			return false;
		}
		return true;
	},
	
	set: function (name, val)
	{
		this.items[name] = val;
	},
	
	get: function (name, onNotFound)
	{
		if (!this.exists(name))
		{
			return onNotFound;
		}
		return this.items[name];
	}
});