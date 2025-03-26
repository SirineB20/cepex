$(document).ready(function () {
    $('.region-content:not(#cont-region-1)').hide();
});
var paths = document.querySelectorAll('.map_list')

paths.forEach(function (path) {
    path.addEventListener('click', function (e) {
        $('.region-content').fadeOut();
        var id = this.id;

        $("#cont-" + id).fadeIn();
    });
});

var tooltip = $('#tooltip');
var map = $('#us-map');
var state = $('.map--svg path');

state.each(function () {

    $(this).on('mouseenter', function () {

        var name = $(this).attr('name');

        tooltip.html(`${name}`).show();

    }).on('mouseleave', function () {
        tooltip.css({ 'left': 0, 'top': 0 }).hide();
    });

});

tooltip.hide();

$(document).on('mousemove', function (e) {

    tooltip.css({
        left: e.pageX + 5,
        top: e.pageY + 5
    });

});

$(document).ready(function(){
	progress_bar();
});

function progress_bar() {
	var speed = 30;
	var items = $('.progress_bar').find('.progress_bar_item');
	
    items.each(function() {
        var item = $(this).find('.progress');
        var itemValue = item.data('progress');
        var i = 0;
        var value = $(this);
		
        var count = setInterval(function(){
            if(i <= itemValue) {
                var iStr = i.toString();
                item.css({
                    'width': iStr+'%'
                });
                value.find('.item_value').html(iStr +'%');
            }
            else {
                clearInterval(count);
            }
            i++;
        },speed);
    });
}