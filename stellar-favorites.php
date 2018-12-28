<?php
/**
 * Plugin Name: Stellar Favorites
 * Description: Allows users to favorites posts
 * Version:     1.0
 * Author: Brandon Jones
 * Text Domain: sfavorites
 */

defined('ABSPATH') or die('No script kiddies please!');
require_once(dirname(__FILE__) . '/inc/functions.php');

/*
 * Enqueue admin scripts and styles
 */
function sfavorites_ajax_tester_style_scripts($hook)
{
    $admin_pages = array(
        'toplevel_page_stellar-favorites',
        'stellar-favorites_page_stellar-favorites-groups',
    );
    //Only enqueue script/styles for plugin settings page
    if ( ! in_array($hook, $admin_pages)) {
        return;
    }

    wp_enqueue_script('ajax-script',
        plugins_url('/js/stellar-favorites-admin.js', __FILE__),
        array('jquery')
    );
    $title_nonce = wp_create_nonce('title_example');
    wp_localize_script('ajax-script', 'sfavorites_admin_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => $title_nonce,
    ));

    wp_enqueue_style('sfavorites-ajax-tester-css', plugins_url('css/sfavorites-admin-styles.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'sfavorites_ajax_tester_style_scripts');

/*
 * Enqueue scripts and styles
 */
function sfavorites_style_scripts()
{
    $enqueueCss = get_option('stellarfavorites_dependencies');
    if ($enqueueCss['css']) {
        wp_enqueue_style('sfavorites-css', plugins_url('css/sfavorites-styles.css', __FILE__));
    }
    wp_enqueue_script('sfavorites-js', plugins_url('js/stellar-favorites.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('sfavorites-js', 'sfavorites_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}

add_action('wp_enqueue_scripts', 'sfavorites_style_scripts');

/*
 * Create the admin menu items
 */
function sfavorites_create_admin_page()
{
    add_menu_page('Stellar Favorites', 'Stellar Favorites', 'edit_pages', 'stellar-favorites.php',
        'sfavorites_admin_page', 'dashicons-star-half', 49);
}

add_action('admin_menu', 'sfavorites_create_admin_page');

function sfavorites_admin_page()
{

    $allPostTypes = getAllPostTypes();
    $dependencies = get_option('stellarfavorites_dependencies');

    if (isset($_POST['submit'])) {
        // Check nonce for security
        if (check_admin_referer('favorite-settings')) {

            update_option('stellarfavorites_display', $_POST['stellarfavorites_display']);
            update_option('stellarfavorites_dependencies', $_POST['stellarfavorites_dependencies']);

            // Display a "settings saved" message on the screen
            echo '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
        }
    }

    include(dirname(__FILE__) . '/views/settings.php');
}

/*
 * Admin group subpage
 */
function sfavorites_create_admin_group_page()
{
    add_submenu_page('stellar-favorites.php', 'Stellar Favorites Group Admin', 'Manage Groups', 'manage_options',
        'stellar-favorites-groups.php', 'sfavorites_admin_group_page');
}

add_action('admin_menu', 'sfavorites_create_admin_group_page');


function sfavorites_admin_group_page()
{

    if (isset($_POST['submit'])) {
        // Check nonce for security
        if (check_admin_referer('favorite-group-settings')) {

            $tempGroups = isset($_POST['stellarfavorites_groups']) ? $_POST['stellarfavorites_groups'] : array();

            if (isset($_POST['stellarfavorites_new_group'])) {
                if ($_POST['stellarfavorites_new_group']['name'] == '' xor $_POST['stellarfavorites_new_group']['slug'] == '') {
                    add_settings_error('admin.php?page=stellar-favorites-groups.php', '130',
                        'You must enter both a name and slug.');
                } elseif ($_POST['stellarfavorites_new_group']['name'] != '' && $_POST['stellarfavorites_new_group']['slug'] != '') {
                    $_POST['stellarfavorites_new_group']['slug'] = strtolower($_POST['stellarfavorites_new_group']['slug']);
                    array_push($tempGroups, $_POST['stellarfavorites_new_group']);
                }
            }

            update_option('stellarfavorites_groups', $tempGroups);

            echo '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
        }
    }

    $groups = get_option('stellarfavorites_groups');

    include(dirname(__FILE__) . '/views/settings-groups.php');
}

/**
 * Add Error Modal container to footer
 */
function add_error_modal()
{
    echo '<div id="sfavoriteModal" class="modal fade" tabindex="-1" role="dialog"  aria-hidden="true"></div>';
}

add_action('wp_footer', 'add_error_modal');

/**
 * Display Shortcode
 */
function get_favorite_button($atts, $content = null)
{
    $a = shortcode_atts(array(
        'post_id'    => '',
        'group_slug' => '',
        'class'      => '',
    ), $atts);

    if ($a['post_id'] == '' || $a['group_slug'] == '') {
        return $output = 'Invalid post or group slug';
    }

    ob_start();
    ?>
    <span class="stellar-favorite <?= $a['class'] ?>" data-postid="<?= $a['post_id'] ?>"
          data-groupslug="<?= $a['group_slug'] ?>"><?php echo ($content != null) ? $content : 'Favorite'; ?></span>
    <?php
    $output = ob_get_clean();

    //be sure to filter output
    return apply_filters('sfavorite_button_output', $output);
}

add_shortcode('sfavorite_button', 'get_favorite_button');

/**
 * Get Favorites List Shortcode
 */
function get_favorite_list($atts)
{
    $a          = shortcode_atts(array(
        'group_slug' => '',
    ), $atts);
    $group_slug = $a['group_slug'];

    $user   = wp_get_current_user();
    $groups = get_option('stellarfavorites_groups');

    $approvedGroups = array();
    foreach ($groups as $group) {
        $approvedGroups[] = $group['slug'];
    }

    //If group slug is not a valid group
    if (($group_slug != '') && ! in_array($group_slug, $approvedGroups)) {
        return $group_slug . ' is not a valid group';
    }

    //Get current user favorites or instantiate empty array
    $currentFavorites = get_user_meta($user->ID, 'stellarfavorites', true);

    ob_start();
    if ($group_slug != '') {
        //Only get group specified
        ?>
        <ul class="favorite-list">
            <?php
            $group = $currentFavorites[$group_slug];

            foreach ($group as $item) {
                $post = get_post($item);
                ?>
                <div class="post-preview">
                    <div class="post-thumbnail">
                        <?php echo get_the_post_thumbnail($post); ?>
                    </div>
                    <div class="post-title">
                        <a href="<?php get_the_permalink($post); ?>"><?php echo $post->post_title; ?> <i
                                    class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php } ?>
        </ul>
        <?php
    } else {
        //Get all groups
        ?>
        <ul class="favorite-list">
            <?php
            foreach ($currentFavorites as $key => $group) {
                ?>
                <li>
                    <?= $key ?>
                    <ul class="favorite-group">
                        <?php foreach ($group as $item) {
                            $post = get_post($item);
                            ?>
                            <div class="post-preview">
                                <div class="post-thumbnail">
                                    <?php echo get_the_post_thumbnail($post); ?>
                                </div>
                                <div class="post-title">
                                    <a href="<?php get_the_permalink($post); ?>"><?php echo $post->post_title; ?> <i
                                                class="fa fa-arrow-right"></i></a>
                                </div>
                            </div>
                        <?php } ?>
                    </ul>
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    }

    return apply_filters('sfavorite_list', ob_get_clean());
}

add_shortcode('sfavorite_list', 'get_favorite_list');

/**
 * Add AJAX Listeners
 */
require_once(dirname(__FILE__) . '/inc/ajax-listeners.php');