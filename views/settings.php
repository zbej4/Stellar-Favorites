<div class="wrap">
    <h2>Stellar Favorites Settings</h2><br/>

    <?php settings_errors() ?>

    <div class="stellar-favorites-display-settings">
        <div class="row">
            <div class="description">
                <h5><?php _e('Shortcode Usage:', 'sfavorites'); ?></h5>
            </div>
            <div class="field">
                <ul>
                    <li>To add a favorite button use the shortcode [sfavorite_button post_id="##" group_slug='##'] in
                        your content or page template.
                    </li>
                    <li>'post_id' is a required parameter and should be the id of the post to be added as a favorite.
                    </li>
                    <li>'group_slug' is optional and can be used to add the post to a particular group of favorites.
                        This group MUST be created in the 'Manage Groups' settings.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('favorite-settings'); ?>
        <h3><?php _e('Enabled Favorites for:', 'favorites'); ?></h3>
        <div class="stellar-favorites-post-types">
            <?php
            foreach ($allPostTypes as $posttype) :
                $post_type_object = get_post_type_object($posttype);
                $display = displayInPostType($posttype);
                ?>
                <div class="post-type-row">
                    <div class="post-type-checkbox">
                        <input type="checkbox"
                               name="stellarfavorites_display[posttypes][<?php echo $posttype; ?>][display]"
                               value="true" <?php if ($display) {
                            echo ' checked';
                        } ?> data-favorites-posttype-checkbox/>
                    </div>
                    <div class="post-type-name">
                        <?php echo $post_type_object->labels->name; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h3><?php _e('Dependencies', 'sfavorites'); ?></h3>
        <div class="stellar-favorites-display-settings">
            <div class="row">
                <div class="description">
                    <h5><?php _e('Enqueue Plugin CSS', 'sfavorites'); ?></h5>
                </div>
                <div class="field">
                    <label class="block"><input type="checkbox" name="stellarfavorites_dependencies[css]" value="true"
                                                data-favorites-dependency-checkbox <?php if ($dependencies['css']) {
                            echo 'checked';
                        } ?> /><?php _e('Output Plugin CSS', 'sfavorites'); ?>
                    </label>
                </div>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>

</div>
<div class="wrap">
    <h3><?php _e('Favorite Debug', 'sfavorites'); ?></h3>
    <div class="stellar-favorites-display-settings">
        <div class="row">
            <div class="description">
                <h5><?php _e('Enter user id to view current favorites', 'sfavorites'); ?></h5>
            </div>
            <div class="field">
                <table id="sfavorites-ajax-table">
                    <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Group Name</th>
                        <th>Posts</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <input type="text" size="4" id="sfavorites-ajax-option-id"/>
                <button id="sfavorites-wp-ajax-button">Get User Favorites</button>
            </div>
        </div>
    </div>
</div>