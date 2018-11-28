<?php

/*
 * The Admin Debug handler function
 */
function sfavorites_ajax_debug_handler()
{
    global $wpdb;

    $id = $_POST['id'];
    //$data = $wpdb->get_row( 'SELECT * FROM wp_options WHERE option_id = ' . $id, ARRAY_A );
    $data               = array(
        'user_id'    => $_POST['id'],
        'meta_key'   => 'stellarfavorites',
        'meta_value' => '',
    );
    $data['meta_value'] = get_user_meta($id, 'stellarfavorites', true);
    echo json_encode($data);
    wp_die(); // just to be safe
}

add_action('wp_ajax_sfavorites_ajax_debug_action', 'sfavorites_ajax_debug_handler');

/**
 * Favorite/Unfavorite button listener for authenticated users
 */
function sfavorites_ajax_favorite_handler()
{
    $user   = wp_get_current_user();
    $groups = get_option('stellarfavorites_groups');

    $approvedGroups = array();
    foreach ($groups as $group) {
        $approvedGroups[] = $group['slug'];
    }

    $data       = array(
        'post_id'    => $_POST['post_id'],
        'group_slug' => $_POST['group_slug'],
    );
    $group_slug = $_POST['group_slug'];

    //If user is not logged in
    if ($user == 0) {
        $data['status'] = 'error';
        $data['error']  = 'login';
        $data['modal']  = getModalBody('Favorite Error', 'You must be logged in to save favorites.');

        echo json_encode($data);
        wp_die();
    }
    //If group slug is not a valid group
    if ( ! in_array($group_slug, $approvedGroups)) {
        $data['status'] = 'error';
        $data['error']  = 'Invalid group slug';

        echo json_encode($data);
        wp_die();
    }

    //Get current user favorites or instantiate empty array
    $currentFavorites = get_user_meta($user->ID, 'stellarfavorites', true);

    if ($currentFavorites == '') {
        //no favorites instantiated
        $currentFavorites = array();
        foreach ($groups as $group) {
            if ($group_slug == $group['slug']) {
                $currentFavorites[$group['slug']] = array($data['post_id']);
            } else {
                $currentFavorites[$group['slug']] = array();
            }
        }
    } else {
        if ( ! in_array($data['post_id'], $currentFavorites[$group_slug])) {
            //If post not already in group array
            //Add post_id to corresponding array by the group_slug
            $currentFavorites[$group_slug][] = $data['post_id'];
        } else {
            //Else post is already a favorite
            //Remove post_id from corresponding array by group_slug
            $key = findInGroup($data['post_id'], $group_slug, $currentFavorites);
            unset($currentFavorites[$group_slug][$key]);
        }

    }
    //Save to user meta
    update_user_meta($user->ID, 'stellarfavorites', $currentFavorites);

    $data['status']    = 'success';
    $data['favorites'] = $currentFavorites;

    //return confirmation
    echo json_encode($data);
    wp_die();
}

add_action('wp_ajax_sfavorites_ajax_favorite_action', 'sfavorites_ajax_favorite_handler');

/**
 * Favorite/Unfavorite button listener for unauthenticated users
 */
function sfavorites_ajax_favorite_login_handler()
{
    $groups = get_option('stellarfavorites_groups');

    $approvedGroups = array();
    foreach ($groups as $group) {
        $approvedGroups[] = $group['slug'];
    }

    $data       = array(
        'post_id'    => $_POST['post_id'],
        'group_slug' => $_POST['group_slug'],
    );
    $group_slug = $_POST['group_slug'];

    //If group slug is not a valid group
    if ( ! in_array($group_slug, $approvedGroups)) {
        $data['status'] = 'error';
        $data['error']  = 'Invalid group slug';

        echo json_encode($data);
        wp_die();
    }

    $data['status'] = 'error';
    $data['error']  = 'login';
    $data['modal']  = getModalBody('Error', 'You must be logged in to save favorites.');

    //return confirmation
    echo json_encode($data);
    wp_die();
}

add_action('wp_ajax_nopriv_sfavorites_ajax_favorite_action', 'sfavorites_ajax_favorite_login_handler');

/**
 * Check favorite ajax function
 */
function sfavorites_ajax_check_favorite_handler()
{
    //If user not logged in return empty array and status unverified
    if ( ! is_user_logged_in()) {
        $data['status'] = 'unauthenticated';
        $data['groups'] = array();

        echo json_encode($data);
        wp_die();
    }

    $user    = wp_get_current_user();
    $post_id = $_POST['post_id'];

    //Get current user favorites
    $currentFavorites = get_user_meta($user->ID, 'stellarfavorites', true);

    //Check if post in user favorite by group
    $activeGroups = array();
    foreach ($currentFavorites as $key => $group) {
        //Add any groups present to array
        if (in_array($post_id, $group)) {
            $activeGroups[] = $key;
        }
    }

    //Return array of group slugs containing the post
    $data = array(
        'status'  => 'authenticated',
        'post_id' => $_POST['post_id'],
        'groups'  => $activeGroups,
    );

    echo json_encode($data);
    wp_die();
}

add_action('wp_ajax_sfavorites_ajax_check_favorite_action', 'sfavorites_ajax_check_favorite_handler');