jQuery.fn.getField = function (name)
{
	var $r = this.getFieldBy(name, "data-field");
	if ($r.size() <= 0)
	{
		$r = this.getFieldBy(name, "data-field-clean");
	}
	return $r;
};
jQuery.fn.getFieldBy = function (name, attrName)
{
	var $th = $(this);
	return $th.find("[" + attrName + "='" + name + "']");
};