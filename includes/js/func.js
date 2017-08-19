function addField() {
	var fieldsAmount = $(".field").length;
	var newFieldAmount = parseInt(fieldsAmount) + 1;
	var fieldHTML = `
	<tr class="field field` + newFieldAmount + `">
		<td>
			<div class="btn-group" data-toggle="buttons">
				<label class="btn btn-default">
					<input type="radio" name="capture_method_` + newFieldAmount + `" class="capture_method" value="selector">selector
				</label>
				<label class="btn btn-default">
					<input type="radio" name="capture_method_` + newFieldAmount + `" class="capture_method" value="regex">regex
				</label>
			</div>
		</td>
		<td>
			<input type="text" class="form-control expression" placeholder="expression" name="expression_` + newFieldAmount + `">
		</td>
		<td>
			<input type="text" class="form-control field_name" placeholder="field name" name="field_name_` + newFieldAmount + `">
		</td>
		<td>
			<button type="button" class="btn btn-danger removeFieldBtn" onclick="removeField(` + newFieldAmount + `);"><span class="glyphicon glyphicon-remove"></span></button>
		</td>
	</tr>
	`;
	$('#fields_table tr:last').after(fieldHTML);
}
function removeField(fieldNr) {
	$(".field" + fieldNr).remove();
	reArrangeFields();
}
function reArrangeFields() {
	/* 
	-- function declaration --
	Problem:
	You have the fields 1-2-3.
	You remove field 2.
	You add a new field.
	But now you get duplicate fields 3
	
	Fix: re-arrange all fields to match the 1-2-3 pattern
	*/
	var fields = $(".field");
	for (var i=0; i<fields.length; i++) {
		$(fields[i]).attr('class', 'field field' + (i+1));
		$(".capture_method", fields[i]).attr("name", 'capture_method_' + (i+1));
		$(".expression", fields[i]).attr("name", 'expression_' + (i+1));
		$(".field_name", fields[i]).attr("name", 'field_name_' + (i+1));
		$(".removeFieldBtn", fields[i]).attr("onclick", "removeField(" + (i+1) + ");");

	}
}
function showUserAgentOption() {
	$(".settings-user-agent").show();
}
function hideUserAgentOption() {
	$(".settings-user-agent").hide();	
}
