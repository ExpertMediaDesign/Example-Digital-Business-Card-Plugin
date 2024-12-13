<?php
/**
 * CPT Name: Personal Sites 2nd Layout
 * Template Post Type: post, page, psites
 */

// Adding a body class
add_filter( 'body_class', 'genesis_emd_psites_body_class' );
function genesis_emd_psites_body_class( $classes ) {

    $classes[] = 'personalsite';
    $classes[] = 'style2';    
    $classes[] = 'testemd';    
    return $classes;

}


// Removes site header elements.
remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
remove_action( 'genesis_header', 'genesis_do_header' );
remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );

// Removes navigation.
remove_theme_support( 'genesis-menus' );

// Removes site footer elements.
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );


// Removes the post title & date from header area
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );


// Creating our page
add_action( 'genesis_before_content', 'emd_psites_insert_content' );
function emd_psites_insert_content() {
            
    global $post;
    $current_post_id = get_the_ID();
    $author_id = $post->post_author;

    // Check if the author is a premium member & set up premium functionality
    // Defined in Functions.php of theme
    $is_premium = emd_pmpro_is_premium_member($author_id);
    $amelia_employee_id = emd_amelia_get_employee_id_from_user($author_id);
    


    // Map our post data
    $post_name = get_post_field( 'post_name', $current_post_id );
    $featured_img_url = get_the_post_thumbnail_url( null, 'full' );  

    $name = get_post_meta( $current_post_id, '_emd_name', true );
    $title = get_post_meta( $current_post_id, '_emd_title', true );
    $phone = get_post_meta( $current_post_id, '_emd_phone', true );
    $email = get_post_meta( $current_post_id, '_emd_email', true );

    $website = get_post_meta( $current_post_id, '_emd_website', true );
    $company = get_post_meta( $current_post_id, '_emd_company', true );
    $add1 = get_post_meta( $current_post_id, '_emd_add1', true );
    $add2 = get_post_meta( $current_post_id, '_emd_add2', true );
    $city = get_post_meta( $current_post_id, '_emd_city', true );
    $state = get_post_meta( $current_post_id, '_emd_state', true );
    $zip = get_post_meta( $current_post_id, '_emd_zip', true );

    $desc = get_post_meta( $current_post_id, '_emd_desc', true );
    $fb = get_post_meta( $current_post_id, '_emd_fb', true );
    $linkedin = get_post_meta( $current_post_id, '_emd_linkedin', true );
    $insta = get_post_meta( $current_post_id, '_emd_insta', true );


    // Design elements
    $color1 = get_post_meta( $current_post_id, '_emd_color1', true );
    $color2 = get_post_meta( $current_post_id, '_emd_color2', true );

    // If no primary color, set a default
    if( empty($color1) ) {
      $color1 = '#1e73be';
    }

    // If no secondary color, use a lighter version of the primary color
    if( empty($color2) ) {
      
      $col = Array(
          hexdec(substr($color1,1,2)),
          hexdec(substr($color1,3,2)),
          hexdec(substr($color1,5,2))
      );

      // $darker = Array(
      //     $col[0]/2,
      //     $col[1]/2,
      //     $col[2]/2
      // );
      $lighter = Array(
          255-(255-$col[0])/2,
          255-(255-$col[1])/2,
          255-(255-$col[2])/2
      );

      // $darker = "#".sprintf("%02X%02X%02X", $darker[0], $darker[1], $darker[2]);
      $color2 = "#".sprintf("%02X%02X%02X", $lighter[0], $lighter[1], $lighter[2]);

    }

    // Convert the colors into a gradient
    $gradient = 'background: linear-gradient(60deg, '.$color1.' 0%, '.$color1.' 50%, '.$color2.' 100%)';





    // Start echoing output for page
    echo '<div class="vcard-template style2" id="rootElement">
              <div class="bgd-shadow"></div>
              <div class="page-home page">';
     

      // Header & Adding gradient color
      echo '<div class="vcard-header" style="'.$gradient.'">';
        echo '<div class="vcard-header-wrapper">
                  <div class="vcard-top-info">';

          // Profile photo - featured image
          echo '<h4 class="top"></h4>';

          if (!empty($featured_img_url)) {
            echo '<div class="profile-img">';
            echo the_post_thumbnail('thumbnail');
            echo '</div>';
            // echo '<div class="img" style="background-image: url("'.$featured_img_url.'");"></div>';
          }
          echo '<h2 class="name dynamicTextColor">'.esc_attr($name).'</h2>';
          
          if (!empty($title)) {
            echo '<h5 class="title dynamicTextColor">'.esc_attr($title).'</h5>';
          }
          if (!empty($company)) {
            echo '<h5 class="title dynamicTextColor">'.esc_attr($company).'</h5>';
          }
          // Icon table - Uses Personal Info
          echo '<div class="vcard-functions"><div class="vcard-functions-wrapper">';
            echo '<a class="vcard-call-header" href="mailto:'.esc_attr($email).'"><div class="vcard-function-call">';
              echo '<i class="fa-solid fa-envelope dynamicTextColor"></i>
                        <small class="dynamicTextColor">Email</small>';
            echo '</div></a>';
            echo '<a class="vcard-call-header" href="tel:+1'.esc_attr($phone).'"><div class="vcard-function-email">';
              echo '<i class="fa-solid fa-phone dynamicTextColor"></i>
                        <small class="dynamicTextColor">Call</small>';
            echo '</div></a>';
            if ($is_premium) {
            echo '<a class="vcard-call-header" href="#amelia-v2-booking-1000" class="booking-calendar"><div class="vcard-function-email">';
              echo '<i class="fa-solid fa-calendar dynamicTextColor"></i>
                        <small class="dynamicTextColor">Schedule</small>';
            echo '</div></a>';
            }
          echo '</div></div>';

        echo '</div></div>';
      echo '</div>';

      // Body
      echo '<div class="vcard-body"><div class="vcard-body-wrapper"><div class="vcard-body-padding">';

        if (!empty($desc)) {
          echo '<div class="vcard-row">';
            echo '<p class="description">'.esc_attr($desc).'</p>';
          echo '</div>';
        }
        
        

        // 
        echo '<div class="vcard-seperator"></div>';

        echo '<div class="vcard-row">';
          echo '<h4><a href="tel:+1'.esc_attr($phone).'" style="color: '.$color1.'"><i class="fa-solid fa-phone"></i>'.esc_attr($phone).'</a></h4>
                    <small>Phone Number</small>';
        echo '</div>';
        echo '<div class="vcard-row">';
          echo '<h4><a href="mailto:'.esc_attr($email).'" style="color: '.$color1.'"><i class="fa-solid fa-envelope"></i>'.esc_attr($email).'</a></h4>
                    <small>Email Address</small>';
        echo '</div>';

        if (!empty($add1)) {
          echo '<div class="vcard-row">'; 
            // Format the Google Maps link 
            $gmaps = 'https://maps.google.com/?q='.esc_attr($add1).'+'.esc_attr($add2).'+'.esc_attr($city).'+'.esc_attr($state).'+'.esc_attr($zip);
            $gmaps = preg_replace('/\s+/','+',$gmaps);

            echo '<h4><a href="'.$gmaps.'" target="_blank" style="color: '.$color1.'"><i class="fa-solid fa-earth-americas"></i>'.esc_attr($add1).', '.esc_attr($add2).'</br>'.esc_attr($city).', '.esc_attr($state).' '.esc_attr($zip).'</a></h4>
                      <small>Office Address</small>';
          echo '</div>';
        }
        if (!empty($website)) {
          echo '<div class="vcard-row">';
            echo '<h4><a href="'.esc_attr($website).'"  style="color: '.$color1.'"><i class="fa-solid fa-globe"></i>'.esc_attr($website).'</a></h4>
                      <small>Website</small>';
          echo '</div>';
        }

        // Start social area
        echo '<div class="vcard-social" style="margin-bottom:20px;">';

        if (!empty($linkedin) && !empty($fb) && !empty($insta)) {
          echo '<div class="socialMedia-container">
              <div class="channels-padding mt-0">';
          if (!empty($linkedin)) {
          echo '<a href="'.esc_attr($linkedin).'" class="channel-container">
                    <div class="table-cell-middle pl-55 pos-relative">
                        <div class="channel-bgd-linkedin">
                        <i class="fa-brands fa-linkedin"></i>
                        </div>
                    </div>
                </a>';
          }
          if (!empty($fb)) {
          echo '<a href="'.esc_attr($fb).'" class="channel-container">
                    <div class="table-cell-middle pl-55 pos-relative">
                        <div class="channel-bgd-facebook">
                        <i class="fa-brands fa-facebook"></i>
                        </div>
                    </div>
                </a>';
          }
          if (!empty($insta)) {
          echo '<a href="'.esc_attr($insta).'" class="channel-container">
                    <div class="table-cell-middle pl-55 pos-relative">
                        <div class="channel-bgd-insta">
                        <i class="fa-brands fa-instagram"></i>
                        </div>
                    </div>
                </a>';
          }
        echo '</div></div>';
        }
        echo '</div>';

        // Vcard download
        echo '<div class="vcard-download follow-scroll contactData-container ">';
        echo '<a href="/wp-content/plugins/emd-personal-sites/vcard/'.$post_name.'.vcf" class="vcard-button" style="background: '.$color1.'">
                        <div class="vcard-label">
                        <i class="fa-solid fa-download"></i>
                        <h4>Download VCard</h4>
                        </div>
                </a>';

      // Close up body section
      echo '</div></div></div>';
      
      // Amelia Shortcode
      if($is_premium) {
        echo do_shortcode( '[ameliastepbooking service=1 employee='.$amelia_employee_id.']' ); 
      }
      

    // Close the page
    echo '</div></div>';



    


    

 

}


// Runs the Genesis loop.
genesis();