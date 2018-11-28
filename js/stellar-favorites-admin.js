jQuery(document).ready(function($) {

  //Event Listener for Debug
  $('#sfavorites-wp-ajax-button').click(function() {
    var id = $('#sfavorites-ajax-option-id').val();
    $.ajax({
      method: 'POST',
      url: ajaxurl,
      data: {'action': 'sfavorites_ajax_debug_action', 'id': id},
    }).done(function(data) {
      console.log('Successful AJAX Call! /// Return Data:');
      console.log(data);
      data = JSON.parse(data);
      $.each(data.meta_value, function(key, group) {
        $('#sfavorites-ajax-table').
            append('<tr><td>' + data.user_id + '</td><td>' + key +
                '</td><td>' + group + '</td></tr>');
      });
    }).fail(function(data) {
      console.log('Failed AJAX Call /// Return Data: ');
      console.log(data);
    });
  });

  //Delete function for group admin
  $('.group-delete').on('click', function(e) {
    var $d = $(this).data('group-delete');
    //Find the correct row
    var $row = $('.groups-table tr[data-group=' + $d + ']');
    $row.remove();
  });

});