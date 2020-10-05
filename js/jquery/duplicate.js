jQuery.fn.duplicate = function ()
{
	return this.clone().appendTo(this.parent());
};