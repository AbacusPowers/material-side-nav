<?php
/**
 * Created by: Justin Maurer for Advisant Group, Inc
 * Date: 4/3/16
 * Time: 10:18 AM
 * Version: 0.1
 */

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class MaterialSideNavAdmin
{
    /**
     * Option key, and option page slug
     * @var string
     */
    private $key = 'material_side_nav_options';
    /**
     * Options page metabox id
     * @var string
     */
    private $metabox_id = 'material_side_nav_option_metabox';
    /**
     * Options Page title
     * @var string
     */
    protected $title = 'Material Side Nav Options';
    /**
     * Options Page hook
     * @var string
     */
    protected $options_page = '';
    /**
     * Holds an instance of the object
     *
     * @var MaterialSideNavAdmin
     **/
    private static $instance = null;

    /**
     * Constructor
     * @since 0.1.0
     */
    private function __construct()
    {
        // Set our title
        $this->title = __('Material Side Nav Options', 'material_side_nav');
    }

    /**
     * Returns the running object
     *
     * @return MaterialSideNavAdmin
     **/
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks()
    {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_options_page'));
        add_action('cmb2_admin_init', array($this, 'addOptionsPageMetabox'));
    }

    /**
     * Register our setting to WP
     * @since  0.1.0
     */
    public function init()
    {
        register_setting($this->key, $this->key);
    }

    /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page()
    {
        $this->options_page = add_menu_page($this->title, $this->title, 'manage_options', $this->key,
            array($this, 'adminPageDisplay'));
        // Include CMB CSS in the head to avoid FOUC
        add_action("admin_print_styles-{$this->options_page}", array('CMB2_hookup', 'enqueue_cmb_css'));
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function adminPageDisplay()
    {
        ?>
        <div class="wrap cmb2-options-page <?php echo $this->key; ?>">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <?php cmb2_metabox_form($this->metabox_id, $this->key); ?>
        </div>
        <?php
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    function addOptionsPageMetabox()
    {
        $prefix = 'material_side_nav_';
        // hook in our save notices
        add_action("cmb2_save_options-page_fields_{$this->metabox_id}", array($this, 'settings_notices'), 10, 2);
        $cmb = new_cmb2_box(array(
            'id' => $this->metabox_id,
            'hookup' => false,
            'cmb_styles' => false,
            'show_on' => array(
                // These are important, don't remove
                'key' => 'options-page',
                'value' => array($this->key)
            )
        ));
        // Set our CMB2 fields
        $cmb->add_field(array(
            'name' => __('Background Color', 'material_side_nav'),
            'desc' => __('Select your primary color', 'material_side_nav'),
            'id' => $prefix . 'side_nav_background_colorpicker',
            'type' => 'colorpicker',
            'default' => '#904199'
        ));
        $cmb->add_field( array(
            'name'    => 'Logo for drawer',
            'desc'    => 'Upload an image or enter an URL.',
            'id'      => $prefix . 'trigger_image',
            'type'    => 'file',
            // Optional:
            'options' => array(
                'url' => false, // Hide the text input for the url
                'add_upload_file_text' => 'Add File (jpeg or png)' // Change upload button text. Default: "Add or Upload File"
            ),
        ) );
        $postNewItemField = $cmb->add_field(array(
            'id'            => $prefix . 'side_nav_item',
            'type'          => 'group',
            'description'   => __( 'Enter a URL or link type', 'material_side_nav' ),
            'repeatable'    => true,
            'options'       => array(
                'group_title'   => __( 'Side Nav Menu Item {#}', 'material_side_nav'),
                'add_button'    => __( 'New Menu Item', 'material_side_nav'),
                'remove_button' => __( 'Remove Menu Item', 'material_side_nav'),
                'sortable'      => true
            )
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('Label', 'material_side_nav'),
            'desc' => __('Label for menu item', 'material_side_nav'),
            'id' => 'side_nav_label',
            'type' => 'text'
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name'             => 'URL method',
            'id'               => 'url_method',
            'type'             => 'radio',
            'show_option_none' => false,
            'options'          => array(
//                'post-type' => __( 'Post Type', 'material_side_nav' ),
                'url'   => __( 'URL', 'material_side_nav' ),
                'variable-url'     => __( 'URL with variable', 'material_side_nav' )
            )
        ));
//        $cmb->add_group_field($postNewItemField, array(
//            'name'  => 'Post Type',
//            'id'    => 'post_type',
//            'desc'  => 'Choose a post type from the dropdown',
//            'type'  => 'select',
//            'show_option_none' => true,
//            'default' => 'None - Use URL',
//            'options' => 'getAllPostTypes'
//        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('Static URL', 'material_side_nav'),
            'desc' => __('Enter URL', 'material_side_nav'),
            'id' => 'side_nav_url',
            'type' => 'text_url',
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('URL with custom variable', 'material_side_nav'),
            'desc' => __('If session-related variables are required, use {#} in place of the variable. For example: "/members/{#}/profile". ', 'material_side_nav'),
            'id' => 'side_nav_variable_url',
            'type' => 'text'
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name'             => 'Variable to use',
            'desc'             => 'Select which item to insert into URL.',
            'id'               => 'side_nav_variable',
            'type'             => 'select',
            'show_option_none' => true,
            'options'          => array(
                'none'     => __( 'none', 'material_side_nav' ),
                'user-slug' => __( 'Logged-in User Slug', 'material_side_nav' ),
                'user-id'   => __( 'Logged-in User ID', 'material_side_nav' ),
                'bp-user-slug' => __( 'Logged-in User\'s BuddyPress Slug', 'material_side_nav' ),
            )
        ));
    }

    /**
     * Register settings notices for display
     *
     * @since  0.1.0
     * @param  int $object_id Option key
     * @param  array $updated Array of updated fields
     * @return void
     */
    public function settings_notices($object_id, $updated)
    {
        if ($object_id !== $this->key || empty($updated)) {
            return;
        }
        add_settings_error($this->key . '-notices', '', __('Settings updated.', 'material_side_nav'), 'updated');
        settings_errors($this->key . '-notices');
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
    public function __get($field)
    {
        // Allowed fields to retrieve
        if (in_array($field, array('key', 'metabox_id', 'title', 'options_page'), true)) {
            return $this->{$field};
        }
        throw new Exception('Invalid property: ' . $field);
    }

}

/**
 * Helper function to get/return the MaterialSideNavAdmin object
 * @since  0.1.0
 * @return MaterialSideNavAdmin object
 */
function materialSideNavAdmin()
{
    return MaterialSideNavAdmin::getInstance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key Options array key
 * @return mixed        Option value
 */
function materialSideNavGetOption($key = '')
{
    return cmb2_get_option(materialSideNavAdmin()->key, $key);
} 