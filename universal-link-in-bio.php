<?php
/**
 * Plugin Name: Universal Link in bio
 * Author URI: https://talbi-consept.com
 * Description: Plugin to add a static Link in bio url to your website
 * Version: 1.0.1
 * Author: Talbi ConSept
 * Text Domain: universal-link-in-bio
*/




/*
* Plugin activation function.
*/
register_activation_hook(__FILE__, 'universal_link_in_bio_activate');
function universal_link_in_bio_activate(){
    add_rewrite_rule(
        'linkinbio/?$',
        'index.php?pagename=linkinbio',
        'top'
    );
    flush_rewrite_rules();
}


/*
* Plugin deativation function.
*/
register_deactivation_hook( __FILE__, 'universal_link_in_bio_deactivate' );
function universal_link_in_bio_deactivate() {
    flush_rewrite_rules();
}


/*
* Load plugin textdomain.
*/
add_action( 'init', 'universal_link_in_bio_load_textdomain' );
function universal_link_in_bio_load_textdomain() {
    load_plugin_textdomain( 'universal-link-in-bio', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}


/*
* Plugin redirect function.
*/
add_action( 'pre_get_posts', 'universal_link_in_bio_redirect' );
function universal_link_in_bio_redirect( $query ){
    if($query->is_main_query() && is_page() ){
        if($query->get( 'pagename' ) == 'linkinbio'){
            $universal_link_in_bio_enabled = get_option('universal_link_in_bio_enabled');
            if($universal_link_in_bio_enabled){
                $universal_link_in_bio_redirect_url = get_option('universal_link_in_bio_redirect_url') ? get_option('universal_link_in_bio_redirect_url') : '/';
                wp_redirect($universal_link_in_bio_redirect_url);
                exit;
            }
        }
    }
}


/*
* Plugin settings page function.
*/
add_action( 'admin_menu', 'universal_link_in_bio_add_settings_page' );
function universal_link_in_bio_add_settings_page() {
    add_options_page( 'Link in bio settings', 'Universal Link in bio', 'manage_options', 'universal-link-in-bio', 'universal_link_in_bio_render_settings_page' );
}


/*
* Add "settings" button to plugins list.
*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__) , 'universal_link_in_bio_settings_link' );
function universal_link_in_bio_settings_link( $links ) {
    $links[] = '<a href="' .
    admin_url( 'options-general.php?page=universal-link-in-bio' ) .
    '">' . __('Settings') . '</a>';
    return $links;
}


/*
* Plugin ajax update settings function.
*/
add_action('wp_ajax_universal_link_in_bio_update_settings', 'universal_link_in_bio_update_settings');
function universal_link_in_bio_update_settings(){
    $success = $errors = null;
    if(!current_user_can('manage_options') && !current_user_can('manage_options')) $errors[] = __("You don't have permissions", 'universal-link-in-bio');
    if(!$errors){
        $enabled = sanitize_text_field($_POST['universal_link_in_bio_enabled']) == "true" ? true : false;
        $url = sanitize_text_field($_POST['universal_link_in_bio_redirect_url']);
        update_option('universal_link_in_bio_enabled', $enabled);
        update_option('universal_link_in_bio_redirect_url', $url);
        $success = true;
    }
    echo json_encode(array(
        'success' => $success,
        'errors' => $errors
    ));
    wp_die();
}


/*
* Plugin settings page content function.
*/
function universal_link_in_bio_render_settings_page() {
    $universal_link_in_bio_enabled = get_option('universal_link_in_bio_enabled');
    $universal_link_in_bio_redirect_url = get_option('universal_link_in_bio_redirect_url');
    ?>
    <style>
        .universal_link_in_bio_rating_prompt {
            margin-top: 30px;
        }
    </style>
    <h2><?php esc_attr_e( 'Plugin settings', 'universal-link-in-bio' ); ?></h2>
    <div class="text">
        <?php esc_attr_e( 'This plugin allows you to have a static url that you can put on your bio and it will redirect to the link specified bellow. You will never have to change the link in your bio !', 'universal-link-in-bio' ); ?>
    </div>
    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="universal_link_in_bio_enabled"><?php esc_attr_e( 'Enabled' ); ?></label></th>
                    <td><input class="regular-text" type="checkbox" id="universal_link_in_bio_enabled" <?php echo $universal_link_in_bio_enabled ? 'checked' : ''; ?> name="universal_link_in_bio_enabled" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="universal_link_in_bio_redirect_url"><?php esc_attr_e( 'Redirect URL', 'universal-link-in-bio' ); ?><small>(<?php esc_attr_e( 'Empty redirects to home page', 'universal-link-in-bio' ); ?>)</small></label></th>
                    <td><input class="regular-text" type="text" id="universal_link_in_bio_redirect_url" name="universal_link_in_bio_redirect_url" value="<?php esc_attr_e($universal_link_in_bio_redirect_url); ?>" placeholder="<?php esc_attr_e( 'Example', 'universal-link-in-bio' ); ?>: <?php esc_attr_e( 'blog', 'universal-link-in-bio' ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="universal_link_in_bio_redirect_url"><?php esc_attr_e( 'Link to put in your bio', 'universal-link-in-bio' ); ?></label></th>
                    <td><input class="regular-text" type="text" readonly id="universal_link_in_bio_url" name="universal_link_in_bio_url" value="<?php echo get_site_url(); ?>/linkinbio" /></td>
                </tr>
            </tbody>
        </table>
        <input id="universal_link_in_bio_submit_btn" class="button button-primary" type="button" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <div class="universal_link_in_bio_rating_prompt"><?php esc_attr_e( 'Do you like using this plugin?', 'universal-link-in-bio' ); ?> <a href="https://wordpress.org/plugins/universal-link-in-bio/#reviews" target="_Blank"><?php esc_attr_e( 'Let us a review ;)', 'universal-link-in-bio' ); ?></a></div>
    <script>
        jQuery(document).ready(function($){
            $('#universal_link_in_bio_submit_btn').on('click', function(){
                $('#universal_link_in_bio_submit_btn').prop('disabled', true);
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'universal_link_in_bio_update_settings', universal_link_in_bio_enabled:$('#universal_link_in_bio_enabled').prop('checked'), universal_link_in_bio_redirect_url:$('#universal_link_in_bio_redirect_url').val()}, 
                function(r){
                    if(r.success){
                        alert("<?php esc_attr_e( 'Saved' ); ?>");
                    } else {
                        alert("<?php esc_attr_e( 'Error', 'universal-link-in-bio' ); ?>" + r.errors.join(' | '));
                    }
                    $('#universal_link_in_bio_submit_btn').prop('disabled', false);
                },
                'json');
            });
        });
    </script>
    <?php
}