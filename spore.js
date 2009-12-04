// Namespace
spore_fort = {};

spore_fort.show_creature = function (creature) {
	creature_slot = $('<div class="creature" />').html('<h1>' + creature.name + '</h1>');
	$('<img/>').attr('src', creature.thumb).attr('id', creature.id).attr('rel', creature.name).attr('class', 'draggable').appendTo(creature_slot);
	creature_slot.appendTo('#selector')
}

spore_fort.load_user_creatures = function (user) {
	$.getJSON("proxy.php?action=user&user=" + user, function(data){

		$.each(data, function(i, creature){
			spore_fort.show_creature(creature);
		});

	});

}

spore_fort.get_random_creatures = function (query) {
	$.getJSON("proxy.php?action=random&query=" + query + "&rand=" + Math.floor(Math.random()*10001), function(data){

		$.each(data, function(i, creature){
			spore_fort.show_creature(creature);
		});

		spore_fort.activate_dragging();

	});

}

spore_fort.top_rated_creatures = function (query) {
	$.getJSON("proxy.php?action=toprated", function(data){

		$.each(data, function(i, creature){
			spore_fort.show_creature(creature);
		});

		spore_fort.activate_dragging();

	});

}

spore_fort.add_to_transmuter = function () {
	$.get("spore.php?id=" + ui.draggable.attr('id'), function(data) {
		alert(data);
	})
}



spore_fort.activate_dragging = function () {

	$('.draggable').draggable({
						 helper : 'clone',
						 opacity : 0.5


	});

	$('#transmuter').droppable({
		drop: function(event, ui) {
			return spore_fort.add_to_transmuter($(ui.draggable), $(this));
		}
	});
}

spore_fort.add_to_transmuter = function (creature, target) {
	$('<li/>').attr('rel', $(creature).attr('id')).html($(creature).attr('rel')).appendTo('ul#readylist');
	return true;
}

// Document.ready hook

$(document).ready(function() {

	$("#find_user").bind('click', function() {
		alert('User search bind fired');
		spore_fort.load_user_creatures($('#searchfield').val());
	});

	$("#random").bind('click', function() {
		spore_fort.get_random_creatures();
	});

	$("#toprated").bind('click', function() {
		spore_fort.top_rated_creatures();
	});

	$("#clean").bind('click', function() {
		$("#selector").html('');
	});

	$("#download").bind('click', function() {
		querystring = 'a=1';
		$('#transmuter').find('li').each(function(){
			querystring = querystring + '&creatures[]=' + $(this).attr('rel');
		});

		// Redirect window location to file generation script
		window.location = "spore.php?" + querystring;
	});

});