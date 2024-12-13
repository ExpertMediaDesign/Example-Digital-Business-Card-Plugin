<?php
/**
 * Plugin Name: EMD Personal Sites CPT
 * Plugin URI: https://expertmedia.design
 * Description: Creates the personal sites. Requires Contact Form 7 & Paid Memberships Pro.
 * Version: 0.5
 * Author: Curtis Bickler
 * Author URI: https://expertmedia.design
 * Text Domain: emd_personal_sites
 * License: GPL2
 */

class emdPersonalSites {

    public $plugin_name = 'emd-personal-sites';
    public $txt_domain = 'emd_personal_sites';
    // public $plugin_url = plugin_dir_path( __FILE__ );

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public function  __construct() {

        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'init', array( $this, 'create_custom_post_type' ) );

        add_action( 'add_meta_boxes', array( $this, 'add_admin_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );

        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        add_filter( 'theme_page_templates', array( $this, 'add_page_template_to_options' ) );
        add_filter( 'theme_psites_templates', array( $this, 'add_page_template_to_options' ) );

        add_filter( 'single_template', array( $this, 'assign_custom_template' ) );

        add_filter( 'request', array( $this, 'parse_request_remove_cpt_slug' ), 1, 1 );
        add_filter( 'post_type_link', array( $this, 'remove_cpt_slug'), 10, 3 );


        // Contact Form 7 related hooks
        add_action( 'wpcf7_before_send_mail', array( $this, 'cf7_site_creation'), 10, 1 );

        // Paid Memberships Pro related hooks
        // add_action( 'pmpro_after_change_membership_level', array( $this, 'pmpro_level_change_employee_creation'), 10, 3 );



        // $this->make_shortcode();

    }

    public function register_scripts() {
        
        // CSS
        wp_register_style('emd-personal-sites-default', plugins_url( $this->plugin_name .'/css/psites.css' ), array(), filemtime( plugins_url( $this->plugin_name .'/css/psites.css' ) ), false) ;

    
    } 

    public function load_admin_scripts() {
        
        wp_enqueue_style('emd-personal-sites-admin', plugins_url( $this->plugin_name .'/css/admin.css' ), array(), filemtime(plugins_url( $this->plugin_name .'/css/admin.css' ) ), false) ;

        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_script( 'emdcard-colorpicker', plugins_url( $this->plugin_name .'/js/admin.js'), array( 'wp-color-picker' ), false, true );
    
    }




    /**
     * Page Template Customizations
     * 
     * Need to edit all 3 functions below whenever we add in new Page Templates
     */
    // Setting the CSS & JS for each page template
    // Should register the style/script in the register_scripts function above first
    public function load_frontend_scripts() {

        global $post;

        // Check the page template & assign as needed
        switch( get_page_template_slug( $post ) ) {
            case plugin_dir_path( __FILE__ ) . 'templates/site-template-2.php':
                wp_enqueue_style( 'emd-personal-sites-default' );
                break;
            default:
                wp_enqueue_style( 'emd-personal-sites-default' );
        }
    
    }
    // Telling WP where to look for each page template
    public function assign_custom_template( $single ) {

        global $post;

        if ( $post->post_type == 'psites' ) {

            // Check the page template & assign as needed
            switch( get_page_template_slug( $post ) ) {
                case plugin_dir_path( __FILE__ ) . 'templates/site-template-2.php':
                    return plugin_dir_path( __FILE__ ) . 'templates/site-template-2.php';
                    break;
                default:
                    return plugin_dir_path( __FILE__ ) . 'templates/site-template-default.php';
            }


        }

        return $single;

    }
    // Adding our page template to the selectable options in the Admin editor
    public function add_page_template_to_options( $templates ) {

       $templates[plugin_dir_path( __FILE__ ) . 'templates/site-template-default.php'] = 'Personal Sites Default Template';
       $templates[plugin_dir_path( __FILE__ ) . 'templates/site-template-2.php'] = 'Personal Sites 2nd Template';
     
       return $templates;

    }







    /**
     * Create our Personal Sites Custom Post Type
     */
    public function create_custom_post_type() {

        // Set UI labels for Custom Post Type
        $labels = array(

            'name'                => _x( 'Sites', 'Post Type General Name', $this->txt_domain ),
            'singular_name'       => _x( 'Site', 'Post Type Singular Name', $this->txt_domain ),
            'menu_name'           => __( 'Sites', $this->txt_domain ),
            'parent_item_colon'   => __( 'Parent Site', $this->txt_domain ),
            'all_items'           => __( 'All Sites', $this->txt_domain ),
            'view_item'           => __( 'View Site', $this->txt_domain ),
            'add_new_item'        => __( 'Add New Site', $this->txt_domain ),
            'add_new'             => __( 'Add New', $this->txt_domain ),
            'edit_item'           => __( 'Edit Site', $this->txt_domain ),
            'update_item'         => __( 'Update Site', $this->txt_domain ),
            'search_items'        => __( 'Search Sites', $this->txt_domain ),
            'not_found'           => __( 'Not Found', $this->txt_domain ),
            'not_found_in_trash'  => __( 'Not found in Trash', $this->txt_domain ),

        );
         
        // Set other options for Custom Post Type
         
        $args = array(

            'label'               => __( 'psites', $this->txt_domain ),
            'description'         => __( 'Personal Site Information', $this->txt_domain ),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            'supports'            => array( 
                'title', 
                // 'editor', 
                // 'excerpt', 
                'author', 
                'thumbnail', 
                'page-attributes', // Allows picking of page template in editor
                // 'post-formats',
                // 'revisions', 
                // 'custom-fields',
            ),
            // You can associate this CPT with a taxonomy or custom taxonomy. 
            // 'taxonomies'          => array( 'locations' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 16,
            'menu_icon'           => 'dashicons-id',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',

        );
         
        // Registering the Custom Post Type
        register_post_type( 'psites', $args );
     
    }

    /**
     * Custom fields for our CPT
     */
    public function add_admin_metabox() {

        // If we need to add these to a separate CPT, add that CPT to this array
        $post_types = array( 'psites' );

        foreach ( $post_types  as $post_type ) {
            add_meta_box( 
                'emd-psites-info', 
                __('Personal Site Info', $this->txt_domain), 
                array( $this, 'admin_metabox_emd_psites' ), 
                $post_type, 
                'advanced', 
                'high'
            );
        }
    
    }

    public function admin_metabox_emd_psites() {

        global $post;

        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'emd_sites_nonce', 'emd_sites_nonce' );

        // PSite Data - if we have already, use to fill fields
        $name = get_post_meta( $post->ID, '_emd_name', true );
        $title = get_post_meta( $post->ID, '_emd_title', true );
        $phone = get_post_meta( $post->ID, '_emd_phone', true );
        $email = get_post_meta( $post->ID, '_emd_email', true );
        $website = get_post_meta( $post->ID, '_emd_website', true );
        $company = get_post_meta( $post->ID, '_emd_company', true );
        $add1 = get_post_meta( $post->ID, '_emd_add1', true );
        $add2 = get_post_meta( $post->ID, '_emd_add2', true );
        $city = get_post_meta( $post->ID, '_emd_city', true );
        $state = get_post_meta( $post->ID, '_emd_state', true );
        $zip = get_post_meta( $post->ID, '_emd_zip', true );
        $desc = get_post_meta( $post->ID, '_emd_desc', true );
        $fb = get_post_meta( $post->ID, '_emd_fb', true );
        $linkedin = get_post_meta( $post->ID, '_emd_linkedin', true );
        $insta = get_post_meta( $post->ID, '_emd_insta', true );

        // Style for the PSite
        $color1 = get_post_meta( $post->ID, '_emd_color1', true );
        $color2 = get_post_meta( $post->ID, '_emd_color2', true );


        // Build the html for our fields on the admin side
        $html = '';

        $html .= '<div class="one-half first">';
        $html .= '<label for="emdcard_color1">Select Your Primary Color</label><br>';
        $html .= '<input type="text" value="' . esc_attr( $color1 ) . '" class="emdcard_color1 emd_color_picker" id="emd_color1" name="emd_color1" data-default-color="#effeff" />';
        $html .= '</div>';
        $html .= '<div class="one-half">';
        $html .= '<label for="emdcard_color2">Select Your Secondary Color</label><br>';
        $html .= '<input type="text" value="' . esc_attr( $color2 ) . '" class="emdcard_color2 emd_color_picker" id="emd_color2" name="emd_color2" data-default-color="#effeff" />';
        $html .= '</div>';
        $html .= '<div class="clearfix"></div><br>';

        $html .= '<label for="emd_name">Name</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_name" name="emd_name" value="' . esc_attr( $name ) . '" >';
        $html .= '<div class="clearfix"></div><br>';

        $html .= '<div class="one-half first">';
        $html .= '<label for="emd_company">Company</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_company" name="emd_company" value="' . esc_attr( $company ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-half">';
        $html .= '<label for="emd_title">Title</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_title" name="emd_title" value="' . esc_attr( $title ) . '" >';
        $html .= '</div><div class="clearfix"></div><br>';

        $html .= '<div class="one-third first">';
        $html .= '<label for="emd_phone">Phone</label><br>';
        $html .= '<input type="tel" style="width:100%" id="emd_phone" name="emd_phone" value="' . esc_attr( $phone ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_email">Email</label><br>';
        $html .= '<input type="email" style="width:100%" id="emd_email" name="emd_email" value="' . esc_attr( $email ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_website">Website</label><br>';
        $html .= '<input type="url" style="width:100%" id="emd_website" name="emd_website" value="' . esc_attr( $website ) . '" >';
        $html .= '</div><div class="clearfix"></div><br>';
        
        $html .= '<label for="emd_desc">General Description</label><br>';
        $html .= '<textarea style="width:100%" id="emd_desc" name="emd_desc">' . esc_attr( $desc ) . '</textarea>';
        $html .= '<div class="clearfix"></div><br>';

        $html .= '<hr>';

        $html .= '<label for="emd_add1">Address</label><br>';
        $html .= '<input type="text" style="width:100%;margin-bottom:3px" id="emd_add1" name="emd_add1" value="' . esc_attr( $add1 ) . '" >';
        $html .= '<input type="text" style="width:100%" id="emd_add2" name="emd_add2" value="' . esc_attr( $add2 ) . '" >';
        $html .= '<div class="clearfix"></div>';

        $html .= '<div class="one-third first">';
        $html .= '<label for="emd_city">City</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_city" name="emd_city" value="' . esc_attr( $city ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_state">State</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_state" name="emd_state" value="' . esc_attr( $state ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_zip">Zip</label><br>';
        $html .= '<input type="text" style="width:100%" id="emd_zip" name="emd_zip" value="' . esc_attr( $zip ) . '" >';
        $html .= '</div><div class="clearfix"></div><br>';

        $html .= '<hr>';

        $html .= '<div class="one-third first">';
        $html .= '<label for="emd_fb">Facebook</label><br>';
        $html .= '<input type="url" style="width:100%" id="emd_fb" name="emd_fb" value="' . esc_attr( $fb ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_linkedin">Linkedin</label><br>';
        $html .= '<input type="url" style="width:100%" id="emd_linkedin" name="emd_linkedin" value="' . esc_attr( $linkedin ) . '" >';
        $html .= '</div>';
        $html .= '<div class="one-third">';
        $html .= '<label for="emd_insta">Instagram</label><br>';
        $html .= '<input type="url" style="width:100%" id="emd_insta" name="emd_insta" value="' . esc_attr( $insta ) . '" >';
        $html .= '</div><div class="clearfix"></div><br>';


        echo $html;

    }

    // Validate and save for the metabox data above
    public function save_meta_box_data( $post_id ) {

        // Check if our nonce is set & valid.
        if ( ! isset( $_POST['emd_sites_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['emd_sites_nonce'], 'emd_sites_nonce' ) ) {
            return;
        }

        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Sanitize user input.
        $name = sanitize_text_field( $_POST['emd_name'] );
        $title = sanitize_text_field( $_POST['emd_title'] );
        $phone = preg_replace('/[^0-9]/', '', $_POST['emd_phone']);
        $email = sanitize_email( $_POST['emd_email'] );
        $website = sanitize_url( $_POST['emd_website'] ); 
        $company = sanitize_text_field( $_POST['emd_company'] ); 
        $add1 = sanitize_text_field( $_POST['emd_add1'] ); 
        $add2 = sanitize_text_field( $_POST['emd_add2'] ); 
        $city = sanitize_text_field( $_POST['emd_city'] ); 
        $state = sanitize_text_field( $_POST['emd_state'] ); 
        $zip = preg_replace('/[^0-9]/', '', $_POST['emd_zip']);
        $desc = sanitize_textarea_field( $_POST['emd_desc'] );
        $fb = sanitize_url( $_POST['emd_fb'] );
        $linkedin = sanitize_url( $_POST['emd_linkedin'] );
        $insta = sanitize_url( $_POST['emd_insta'] ); 
        $color1 = sanitize_hex_color( $_POST['emd_color1'] ); 
        $color2 = sanitize_hex_color( $_POST['emd_color2'] ); 


        // Update the meta field in the database.
        update_post_meta( $post_id, '_emd_name', $name );
        update_post_meta( $post_id, '_emd_title', $title );
        update_post_meta( $post_id, '_emd_phone', $phone );
        update_post_meta( $post_id, '_emd_email', $email );
        update_post_meta( $post_id, '_emd_website', $website );
        update_post_meta( $post_id, '_emd_company', $company );
        update_post_meta( $post_id, '_emd_add1', $add1 );
        update_post_meta( $post_id, '_emd_add2', $add2 );
        update_post_meta( $post_id, '_emd_city', $city );
        update_post_meta( $post_id, '_emd_state', $state );
        update_post_meta( $post_id, '_emd_zip', $zip );
        update_post_meta( $post_id, '_emd_desc', $desc );
        update_post_meta( $post_id, '_emd_fb', $fb );
        update_post_meta( $post_id, '_emd_linkedin', $linkedin );
        update_post_meta( $post_id, '_emd_insta', $insta );
        update_post_meta( $post_id, '_emd_color1', $color1 );
        update_post_meta( $post_id, '_emd_color2', $color2 );


        // Creating the VCard file
        // Shamelessly stolen from: https://support.advancedcustomfields.com/forums/topic/dynamically-create-vcard-from-acf-data-and-download-with-button-click/
        $vpost = get_post($post_id);
        $filename = $vpost->post_name.".vcf";
        header('Content-type: text/x-vcard; charset=utf-8');
        header("Content-Disposition: attachment; filename=".$filename);
        $data=null;
        $data.="BEGIN:VCARD\n";
        $data.="VERSION:3.0\n";
        $data.="FN:".$name."\n"; 
        $data.="N;CHARSET=UTF-8:;".$name.";;;\n";
        $data.="TITLE;CHARSET=UTF-8:".$title."\n";
        $data.="ORG:".$company."\n";
        $data.="EMAIL;TYPE=work:".$email."\n";  
        $data.="TEL;TYPE=WORK,VOICE:".$phone."\n"; 
        $data.="ADR;WORK;PREF:".$add1.",".$add2.";".$city.";".$state.";".$zip."\n"; 
        $data.="URL;type=pref:".$website."\n";
        $data.="X-SOCIALPROFILE;TYPE=facebook:".$fb."\n"; 
        $data.="X-SOCIALPROFILE;TYPE=linkedin:".$linkedin."\n"; 
        $data.="X-SOCIALPROFILE;TYPE=instagram:".$insta."\n"; 
        $data.="END:VCARD";
        $filePath = plugin_dir_path( __FILE__ )."/vcard/".$filename; // you can specify path here where you want to store file.
        $file = fopen($filePath,"w");
        fwrite($file,$data);
        fclose($file);



    }


    



    /**
     * Utilities for our CPTs
     */
    // Removing the CPT's slug from url (removing "/psites" from default /psites/pagename)
    public function remove_cpt_slug( $post_link, $post, $leavename ) {

        if ( $post->post_type != 'psites' ) {
            return $post_link;
        } else {
            $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link ?? '' );
            return $post_link;
        }
    }

    // Instruct wordpress on how to find posts from the new permalinks
    public function parse_request_remove_cpt_slug( $query_vars ) {

        // return if admin dashboard 
        if ( is_admin() ) {
            return $query_vars;
        }

        // return if pretty permalink isn't enabled
        if ( ! get_option( 'permalink_structure' ) ) {
            return $query_vars;
        }

        $cpt = 'psites';

        // store post slug value to a variable
        if ( isset( $query_vars['pagename'] ) ) {
            $slug = $query_vars['pagename'];
        } elseif ( isset( $query_vars['name'] ) ) {
            $slug = $query_vars['name'];
        } else {
            global $wp;
            
            $path = $wp->request;

            // use url path as slug
            if ( !empty($path) ) {
                if ( $path && strpos( $path, '/' ) === false ) {
                    $slug = $path;
                } else {
                    $slug = false;
                }
            }
            
        }

        if ( $slug ) {
            $post_match = get_page_by_path( $slug, 'OBJECT', $cpt );

            if ( ! is_admin() && $post_match ) {

                // remove any 404 not found error element from the query_vars array because a post match already exists in cpt
                if ( isset( $query_vars['error'] ) && $query_vars['error'] == 404 ) {
                    unset( $query_vars['error'] );
                }

                // remove unnecessary elements from the original query_vars array
                unset( $query_vars['pagename'] );
        
                // add necessary elements in the the query_vars array
                $query_vars['post_type'] = $cpt;
                $query_vars['name'] = $slug;
                $query_vars[$cpt] = $slug; // this constructs the "cpt=>post_slug" element
            }
        }

        return $query_vars;
    }








    /*
     * Processing form data
     * Creating the new page after /Start form submission
     *
     * If we switch from Contact Form 7, we should only need to adjust our hook & our form data.
     */ 
    public function cf7_site_creation($contact_form) {
        
        $form_id = $contact_form->id();

        // To find the form id, check the URL when editing the form
        if ($form_id == '64') {

            // Get the data from the form submission
            $wpcf7 = WPCF7_ContactForm::get_current();
            $submission = WPCF7_Submission::get_instance();
            $data = $submission->get_posted_data();

            // This checks for the form tag [food-name] if we change from checking form ID
            // if (empty($data['food-name']) || !isset($data['food-name'])) return;

            // Getting the submission user data
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;


            /**
             *  Sanitize the data before adding to db
             */
            $color1 =           sanitize_hex_color($data['color1']);
            $color2 =           sanitize_hex_color($data['color2']);
            
            $user_email =       sanitize_email($data['your-email']);
            $user_phone =       preg_replace('/[^0-9]/', '', $data['your-phone']);
            // $user_phone =       filter_var($data['your-phone'], FILTER_SANITIZE_NUMBER_INT);
            
            $user_fullname =    sanitize_text_field($data['your-name']);
            $user_title =       sanitize_text_field($data['your-title']);
            $user_company =     sanitize_text_field($data['your-company']);

            $user_desc =        sanitize_textarea_field($data['your-message']);

            $user_website =     sanitize_url($data['your-website']);
            $user_facebook =    sanitize_url($data['your-facebook']);
            $user_instagram =   sanitize_url($data['your-instagram']);
            $user_linkedin =    sanitize_url($data['your-linkedin']);

            $user_add1 =        sanitize_text_field($data['your-add1']);
            $user_add2 =        sanitize_text_field($data['your-add2']);
            $user_city =        sanitize_text_field($data['your-city']);
            $user_state =       sanitize_text_field($data['your-state']);
            $user_zip =         preg_replace('/[^0-9]/', '', $data['your-zip']);


            /**
             *  Arrange data for db insert
             */
            $psite_meta = array(
                // '_wp_page_template' => $template_choice,
                '_emd_color1' => $color1,
                '_emd_color2' => $color2,
                '_emd_phone' => $user_phone,
                '_emd_email' => $user_email,
                '_emd_company' => $user_company,
                '_emd_name' => $user_fullname,
                '_emd_title' => $user_title,
                '_emd_desc' => $user_desc,
                '_emd_website' => $user_website,
                '_emd_facebook' => $user_facebook,
                '_emd_instagram' => $user_instagram,
                '_emd_linkedin' => $user_linkedin,
                '_emd_add1' => $user_add1,
                '_emd_add2' => $user_add2,
                '_emd_city' => $user_city,
                '_emd_state' => $user_state,
                '_emd_zip' => $user_zip,
            );



            // Get user by email. If doesn't exist, create one.
            // if ( false == email_exists( $user_email ) ) {
            //     $password = wp_generate_password( 8, false );
            //     $user_id = wp_create_user( $user_fullname, $password, $user_email );
            // } else {
            //     $user = get_user_by( 'email', $user_email );
            //     $user_id = $user->ID;
            // }

            // Creating our personal site
            $new_page_id = wp_insert_post( array(
                'post_title'     => $user_fullname,
                'post_type'      => 'psites',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'   => '',
                'post_status'    => 'publish',
                'page_template'  => plugin_dir_path( __FILE__ ) . 'templates/site-template-default.php',
                'post_author'    => $user_id, // assign to creator
                'meta_input'     => $psite_meta, // insert all metadata
            ) );
        }

        if ($form_id == '195') {

            // Get the data from the form submission
            $wpcf7 = WPCF7_ContactForm::get_current();
            $submission = WPCF7_Submission::get_instance();
            $data = $submission->get_posted_data();

            // This checks for the form tag [food-name] if we change from checking form ID
            // if (empty($data['food-name']) || !isset($data['food-name'])) return;

            // Getting the submission user data
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;


            /**
             *  Sanitize the data before adding to db
             */
            $color1 =           sanitize_hex_color($data['color1']);
            $color2 =           sanitize_hex_color($data['color2']);
            $template_choice =    sanitize_text_field($data['your-template']);
            
            $user_email =       sanitize_email($data['your-email']);
            $user_phone =       preg_replace('/[^0-9]/', '', $data['your-phone']);
            // $user_phone =       filter_var($data['your-phone'], FILTER_SANITIZE_NUMBER_INT);
            
            $user_fullname =    sanitize_text_field($data['your-name']);
            $user_title =       sanitize_text_field($data['your-title']);
            $user_company =     sanitize_text_field($data['your-company']);

            $user_desc =        sanitize_textarea_field($data['your-message']);

            $user_website =     sanitize_url($data['your-website']);
            $user_facebook =    sanitize_url($data['your-facebook']);
            $user_instagram =   sanitize_url($data['your-instagram']);
            $user_linkedin =    sanitize_url($data['your-linkedin']);

            $user_add1 =        sanitize_text_field($data['your-add1']);
            $user_add2 =        sanitize_text_field($data['your-add2']);
            $user_city =        sanitize_text_field($data['your-city']);
            $user_state =       sanitize_text_field($data['your-state']);
            $user_zip =         preg_replace('/[^0-9]/', '', $data['your-zip']);


            /**
             *  Arrange data for db insert
             */
            $psite_meta = array(
                // '_wp_page_template' => $template_choice,
                '_emd_page_template' => $template_choice,
                '_emd_color1' => $color1,
                '_emd_color2' => $color2,
                '_emd_phone' => $user_phone,
                '_emd_email' => $user_email,
                '_emd_company' => $user_company,
                '_emd_name' => $user_fullname,
                '_emd_title' => $user_title,
                '_emd_desc' => $user_desc,
                '_emd_website' => $user_website,
                '_emd_add1' => $user_add1,
                '_emd_add2' => $user_add2,
                '_emd_city' => $user_city,
                '_emd_state' => $user_state,
                '_emd_zip' => $user_zip,
            );



            // Get user by email. If doesn't exist, create one.
            // if ( false == email_exists( $user_email ) ) {
            //     $password = wp_generate_password( 8, false );
            //     $user_id = wp_create_user( $user_fullname, $password, $user_email );
            // } else {
            //     $user = get_user_by( 'email', $user_email );
            //     $user_id = $user->ID;
            // }

            // Need to create a function to map template choice to template file

            // Creating our personal site
            $new_page_id = wp_insert_post( array(
                'post_title'     => $user_fullname,
                'post_type'      => 'psites',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'   => '',
                'post_status'    => 'publish',
                'page_template'  => plugin_dir_path( __FILE__ ) . 'templates/site-template-default.php', // Need to swap with template choice
                'post_author'    => $user_id, // assign to creator
                'meta_input'     => $psite_meta, // insert all metadata
            ) );
        }
    
    }












    // public function make_shortcode() {
    //     add_shortcode( 'create_site_form', array($this, 'frontend_emd_psites') );
    // }


    // public function frontend_emd_psites() {

    //     // $html = 'trying something new';
    //     return $html;

    // }










}

$emdPersonalSites = new emdPersonalSites();
 
?>