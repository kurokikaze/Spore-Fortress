function show_creature(creature) {
	creature_slot = $('<div class="creature" />').html('<h1>' + creature.name + '</h1>');
	$('<img/>').attr('src', creature.thumb).attr('id', creature.id).attr('rel', creature.name).attr('class', 'draggable').appendTo(creature_slot);
	creature_slot.appendTo('#selector')
}

function load_user_creatures(user) {
	$.getJSON("proxy.php?action=user&user=" + user, function(data){

		$.each(data, function(i, creature){
			show_creature(creature);
		});

	});

}

function search_creatures(query) {
	$.getJSON("proxy.php?action=search&query=" + query, function(data){

		$.each(data, function(i, creature){
			show_creature(creature);
		});

		activate_dragging();

	});

}

function top_rated_creatures(query) {
	$.getJSON("proxy.php?action=toprated", function(data){

		$.each(data, function(i, creature){
			show_creature(creature);
		});

		activate_dragging();

	});

}

function add_to_transmuter() {
	$.get("spore.php?id=" + ui.draggable.attr('id'), function(data) {
		alert(data);
	})
}



function activate_dragging() {

	$('.draggable').draggable({
						 helper : 'clone',
						 opacity : 0.5


	});

	$('#transmuter').droppable({
		drop: function(event, ui) {
			return add_to_transmuter($(ui.draggable), $(this));
		}
	});
}

function add_to_transmuter(creature, target) {
	$('<li/>').attr('rel', $(creature).attr('id')).html($(creature).attr('rel')).appendTo('ul#readylist');
	return true;
}

// Document.ready hook

$(document).ready(function() {

	$("#find_user").bind('click', function() {

		alert('User search bind fired');
		load_user_creatures($('#searchfield').val());
	});

	$("#find_word").bind('click', function() {
		search_creatures($('#searchfield').val());
	});

	$("#toprated").bind('click', function() {
		top_rated_creatures($('#searchfield').val());
	});

	$("#clean").bind('click', function() {
		$("#selector").html('');
	});

	$("#download").bind('click', function() {
		querystring = 'a=1';
		$('#transmuter').find('li').each(function(){
			querystring = querystring + '&creatures[]=' + $(this).attr('rel');
		});
//		window.alert(querystring);
		window.location = "spore.php?" + querystring;
	});

});