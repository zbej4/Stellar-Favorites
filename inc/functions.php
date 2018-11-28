<?php

/**
 * Get all registered post types
 */
function getAllPostTypes($return = 'names', $flat_array = false)
{
    $args       = array(
        'public'  => true,
        'show_ui' => true,
    );
    $post_types = get_post_types($args, $return);
    if ( ! $flat_array) {
        return $post_types;
    }
    $post_types_flat = array();
    foreach ($post_types as $key => $value) {
        $post_types_flat[] = $value;
    }

    return $post_types_flat;
}

/**
 * Display favorite button in a given Post Type
 */
function displayInPostType($posttype)
{
    $types = get_option('stellarfavorites_display');
    if ( ! empty($types['posttypes']) && $types !== "") {
        foreach ($types['posttypes'] as $key => $type) {
            if ($key == $posttype && isset($type['display']) && $type['display'] == 'true') {
                return $type;
            }
        }
    }

    return false;
}

/**
 * Get group by slug
 * Returns false if group not found
 */
function getGroup($slug)
{
    $groups = get_option('stellarfavorites_groups');
    foreach ($groups as $group) {
        if ($slug == $group['slug']) {
            return $group;
        }
    }

    return false;
}

/**
 * Search all groups for a given postid
 * @return boolean true if any occurrence found
 */
function searchGroups($value, $groups)
{
    $top    = sizeof($groups) - 1;
    $bottom = 0;
    while ($bottom <= $top) {
        if ($groups[$bottom] == $value) {
            return true;
        } elseif (is_array($groups[$bottom])) {
            if (in_multiarray($value, ($groups[$bottom]))) {
                return true;
            }
        }

        $bottom++;
    }

    return false;
}

/**
 * Search specific group for postid
 * @return mixed key of found value or false if not found
 */
function findInGroup($value, $groupKey, $array)
{
    $groupFavorites = $array[$groupKey];

    foreach ($groupFavorites as $k => $favorite) {
        if ($favorite == $value) {
            return $k;
        }
    }

    return false;
}

/**
 * Get body of modal
 */
function getModalBody($title, $content)
{
    ob_start();
    ?>

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= $title ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $content ?>
            </div>
            <!--
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div> -->
        </div>
    </div>

    <?php
    return ob_get_clean();
}