function addNewRow(elem)
{
	var table_elem = elem.closest("table");
	var last_tr = table_elem.find("tbody tr:last");
	var tbody = jQuery("#"+table_elem.attr("id")+" > tbody");
	var curr_id = tbody.find("tr").length;

	last_tr.clone().appendTo(tbody);
	var attr_id;
	jQuery.each(tbody.find("tr:last input"), function(index, value) {
		var input_elem = tbody.find("tr:last input").eq(index);
		attr_id =  input_elem.attr("id");
		new_id = attr_id.replace(curr_id,curr_id+1);
		input_elem.attr("id",new_id);
		input_elem.attr("name",new_id);
	});

}

function removeRow(elem)
{
	var curr_tr = elem.closest("tr");
	curr_tr.remove();
}


