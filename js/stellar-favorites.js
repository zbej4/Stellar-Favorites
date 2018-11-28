jQuery(document).ready(function($) {

  //Event Listener for Favorite button
  $('.stellar-favorite').click(function() {
    var element = $(this);
    var postID = element.data('postid');
    var groupSlug = element.data('groupslug');

    $.ajax({
      method: 'POST',
      url: sfavorites_ajax_obj.ajax_url,
      data: {
        'action': 'sfavorites_ajax_favorite_action',
        'post_id': postID,
        'group_slug': groupSlug,
      },
    }).done(function(data) {
      console.log('Successful AJAX Call! /// Return Data:');
      data = JSON.parse(data);
      if (data.status == 'error') {
        switch (data.error) {
          case 'login':
            $('#sfavoriteModal').html(data.modal).modal('show');
            break;
          default:
            console.log('Error: ' + data.error);
        }
      }
      else if (data.status == 'success') {
        console.log('Post: ' + data.post_id + '\nGroup: ' + data.group_slug);
        $(element).toggleClass('active');
      }
    }).fail(function(data) {
      console.log('Failed AJAX Call /// Return Data: ');
      console.log(data);
    });
  });

  $('.sfavorite-load-target').on('click', function() {
    var element = $(this);
    var postID = element.data('postid');
    var groups = $('.stellar-favorite[data-postid="' + postID + '"]');

    $.ajax({
      method: 'POST',
      url: sfavorites_ajax_obj.ajax_url,
      data: {
        'action': 'sfavorites_ajax_check_favorite_action',
        'post_id': postID,
      },
    }).done(function(data) {
      console.log('Successful AJAX Call! ///Favorites:');
      data = JSON.parse(data);
      console.log(data);
      if (data.status == 'authenticated') {
        $.each(groups, function(key, value) {
          var group_slug = $(this).data('groupslug');

          //if group in favorite array
          //add active class to corresponding group
          if ($.inArray(group_slug, data.groups) !== -1) {
            $(this).addClass('active');
          }
        });
      }
    }).fail(function(data) {
      console.log('Failed AJAX Call Return Data:');
      console.log(data);
    });
  });

});