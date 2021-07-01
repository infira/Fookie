Function.prototype.jqBindDelay      = function (scope, extraArguments, delay)
{
	var jQueryF = this;
	var timeout;
	var newFunc = function ()
	{
		if (timeout)
		{
			clearTimeout(timeout);
			timeout = null;
		}
		var args = [$(this)]
		args.push(arguments[0]);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		timeout = setTimeout(function ()
		{
			jQueryF.apply(scope, args);
		}, delay);
		return;
	}
	return newFunc;
};
Function.prototype.jqBind           = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var args = [$(this)]
		for (var i in arguments)
		{
			args.push(arguments[i]);
		}
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		return jQueryF.apply(scope, args);
	}
	return newFunc;
};
Function.prototype.jqDeleteCallback = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var $el   = $(this);
		var event = arguments[0];
		var args  = [$el]
		args.push(event);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		//if (Is.empty($el.getVal().trim()))
		//{
		var rowClass = extraArguments[0];
		var $row     = $el.parents(rowClass);
		$el.addClass("deleteBoxIsActive");
		$row.addClass("deleteBoxIsActive");
		var callback = function ($el, row)
		{
			window.setTimeout(function ()
			{
				$el.removeClass("deleteBoxIsActive");
				$row.removeClass("deleteBoxIsActive");
				$row.find(".deleteBox").remove();
			}, 200);
		};
		$el.unbind("blur.deleteBox").bind("blur.deleteBox", callback.bind(null, $el, $row))
		var ok = true;
		if (extraArguments[2] == true && parseInt($row.getField("count").getVal()) > 0)
		{
			ok = false;
		}
		if ($row.find(".deleteBox").length <= 0 && ok)
		{
			var $deleteBox = '<div class="deleteBox"><img src="images/icon_minus_red.png" /></div>';
			$el.parent().addClass("relative");
			$($deleteBox).insertAfter($el);
			
			var deleteCallback = function ($el, $row)
			{
				Utils.Msg.error("Oled kindel, et soovid kustutada", "Kustutatmine",
				{
					ok: {
						text : "Jah",
						cls  : "red_btn",
						click: function ()
						{
							$el.removeClass("deleteBoxIsActive");
							$row.removeClass("deleteBoxIsActive");
							jQueryF.apply(scope, args)
							Utils.Msg.close();
						}
					}
				});
			};
			$row.find(".deleteBox").bind("click", deleteCallback.bind(null, $el, $row));
			
			/*
			 if (event.type == "keyup" && event.keyCode == 8 && $el.data("jqDeleteCallbackLastKeyCode") === 8) //backspace
			 {
			 var t = new Date().getTime() - $el.data("jqDeleteCallbackLastKeyCodeTime");
			 if (t < 400)
			 {
			 Utils.Msg.error("Oled kindel, et soovid kustutada","Kustutatmine!")
			 $el.data("jqDeleteCallbackLastKeyCode", false);
			 $el.data("jqDeleteCallbackLastKeyCodeTime", 0);
			 return false;
			 }
			 
			 }
			 $el.data("jqDeleteCallbackLastKeyCode", event.keyCode);
			 $el.data("jqDeleteCallbackLastKeyCodeTime", new Date().getTime());
			 */
		}
		//}
		//else
		//{
		//$el.parent().find(".deleteBox").remove();
		//}
	}
	return newFunc;
};
Function.prototype.jqOnEnter        = function (scope, extraArguments)
{
	var jQueryF = this;
	var newFunc = function ()
	{
		var $el   = $(this);
		var event = arguments[0];
		var args  = [$el]
		args.push(event);
		if (typeof extraArguments == "object")
		{
			args = args.concat(extraArguments);
		}
		if (event.keyCode == 13)
		{
			jQueryF.apply(scope, args);
		}
	}
	return newFunc;
};
String.prototype.jq                 = function ()
{
	return jQuery(this.valueOf());
};