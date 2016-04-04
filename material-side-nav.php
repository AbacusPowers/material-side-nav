<?php
/*
Plugin Name: Material Design Side Nav
Plugin URI:  http://advisantgroup.com
Description: Add a material design SideNav/drawer-style menu to your WordPress site
Version:     1.0.0
Author:      Justin Maurer
Author URI:  http://advisantgroup.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: material_post
*/

/*
 * Require necessary libraries
 */
define('MATERIAL_SIDE_NAV_PATH', plugin_dir_path(__FILE__));

if (file_exists(__DIR__ . '/vendor/CMB2/init.php')) {
    require_once __DIR__ . '/vendor/CMB2/init.php';
}
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/5702d59be6df02941647599a/',
    __FILE__,
    'material-side-nav',
    1
);

include(MATERIAL_SIDE_NAV_PATH . 'options-page.php');

/**
 * Activation hook
 */
function materialSideNavActivation()
{
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'materialSideNavActivation');

/**
 * Deactivation hook
 */
function materialSideNavDeactivation()
{
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'materialSideNavDeactivation');

/**
 * Load styles, scripts and fonts
 */
function materialSideNavNewScripts()
{
    /**
     * Register styles to be used
     */

    wp_register_style('material-design-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', '', null);

    wp_register_style('material-side-nav-materialize-css', plugin_dir_url(__FILE__) . '/vendor/materialize-src/materialize.css',
        'material-design-icons', null);

    wp_register_script('material-side-nav-materialize-js', plugin_dir_url(__FILE__) . '/vendor/materialize-src/js/bin/materialize.min.js',
        'jquery', true);

    wp_register_style('material-side-nav-styles', plugin_dir_url(__FILE__) . 'material-side-nav-styles.css',
        'material-design-icons', null);

    wp_register_script('material-side-nav-js', plugin_dir_url(__FILE__) . 'js/material-side-nav.js', 'jquery', null, true);

    /**
     * Enqueue all dependencies
     */

    wp_enqueue_script('jquery');

    wp_enqueue_script('jquery-ui-core');

    wp_enqueue_script('jquery-ui-effects-core');

    wp_enqueue_style('material-design-icons');

    wp_enqueue_style('material-side-nav-materialize-css');

    wp_enqueue_script('material-side-nav-materialize-js');

    wp_enqueue_style('material-side-nav-styles');

    wp_enqueue_script('material-side-nav-js');

}

add_action('wp_enqueue_scripts', 'materialSideNavNewScripts');

/**
 * Build options page
 */

materialSideNavAdmin();

function materialSideNavContent()
{
    $navItems = materialSideNavGetOption('material_side_nav_side_nav_item');
    $logoSrc = materialSideNavGetOption('material_side_nav_trigger_image');
    $primaryColor = materialSideNavGetOption('material_side_nav_side_nav_background_colorpicker');
    $textColor = materialSideNavGetOption('material_side_nav_side_nav_text_colorpicker');
    $topMargin = materialSideNavGetOption('material_side_nav_side_nav_top_margin');
//    var_dump('<pre>',$navItems,'</pre>');
?>
    <nav id="slide-out" class="side-nav" style="background-color: <?= $primaryColor; ?>">
        <ul style="max-height: 100vh;">
            <?php
                foreach ($navItems as $item) {
                    $label = $item['side_nav_label'];
                    $link = materialSideNavGetURL($item);
                    echo '<li style="border-color: '.$textColor.';"><a href="' . $link . '" style="color:' . $textColor . '">' . $label . '</a></li>';
                }
            ?>

        </ul>
        <a href="#" data-activates="slide-out" class="button-collapse" style="background-color: <?= $primaryColor; ?>"><img src="<?= $logoSrc; ?>" id="material-side-nav-logo" width="40"></a>
    </nav>
    <?php
}

add_action('wp_footer', 'materialSideNavContent');

function materialSideNavGetURL(Array $option)
{
    $method = $option['url_method'];
    $url = $option['side_nav_url'];
    $variableURL = $option['side_nav_variable_url'];

    $variableType = $option['side_nav_variable'];

    if ($method === 'variable-url') {
        $currentUserID = get_current_user_id();
        $nicename = get_userdata($currentUserID)->user_nicename;
        $bpUserSlug = bp_core_get_username($currentUserID);
        $pattern = '/({#})/';
        switch ($variableType) {
            case 'user-id':
                $variable = $currentUserID;
                break;
            case 'user-slug':
                $variable = $nicename;
                break;
            case 'bp-user-slug':
                $variable = $bpUserSlug;
                break;
        }


        $link = preg_replace($pattern, $variable, $variableURL);
    } else {
        $link = $url;
    }
    return $link;
}