<div class="wrap">
    <h2>Stellar Favorites Group Admin</h2><br />
    <?php settings_errors() ?>
    <h3><?php _e('Manage Groups', 'sfavorites'); ?></h3>
    <form method="post" action="">
        <?php wp_nonce_field( 'favorite-group-settings' ); ?>
        <div class="stellar-favorites-display-settings">
            <div class="row">
                <div class="field">

                    <table id="sfavorites-ajax-table" class="groups-table">
                        <thead>
                        <tr>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Group Slug</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach( $groups as $key => $group ){
                            ?>
                            <tr data-group="<?=$key?>">
                                <td><?=$key?></td>
                                <td><input name="stellarfavorites_groups[<?=$key?>][name]" readonly type="text" value="<?=$group['name']?>" /></td>
                                <td><input name="stellarfavorites_groups[<?=$key?>][slug]" readonly type="text" value="<?=$group['slug']?>" /></td>
                                <td><span class="group-delete" data-group-delete="<?=$key?>">Delete</span></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr class="custom-new">
                            <td>Add New:</td>
                            <td><input name="stellarfavorites_new_group[name]" type="text" placeholder="Favorites" /></td>
                            <td><input name="stellarfavorites_new_group[slug]" type="text" placeholder="favorites" /></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        <?php submit_button(); ?>
    </form>
</div>