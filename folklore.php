<?php
/*
Plugin Name: Folklore Plugin
Description: Displays UW directory with filtering, searching, and grid/list views.
Version: 1.0
Author: UW
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

function register_directory_post_type() {
    $args = array(
        'labels' => array(
            'name'               => 'Directory Entries',
            'singular_name'      => 'Directory Entry',
            'add_new'            => 'Add New Entry',
            'add_new_item'       => 'Add New Directory Entry',
            'edit_item'          => 'Edit Directory Entry',
            'new_item'           => 'New Directory Entry',
            'view_item'          => 'View Directory Entry',
            'search_items'       => 'Search Directory Entries',
            'not_found'          => 'No entries found',
            'not_found_in_trash' => 'No entries found in Trash',
            'all_items'          => 'All Directory Entries',
            'menu_name'          => 'Directory',
        ),
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-id',
        'supports'     => array( 'title', 'editor' ),
        'show_in_rest' => true,
    );

    register_post_type( 'directory_entry', $args );
}
add_action( 'init', 'register_directory_post_type' );

function uw_directory_enqueue_scripts() {
    // Vendor libs
    wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' );
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css' );

    wp_enqueue_script( 'isotope-js', 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );

    // Plugin assets
    wp_enqueue_style( 'uw-directory-style', plugins_url( '/folklore.scss', __FILE__ ) );
    wp_enqueue_script( 'uw-directory-script', plugins_url( '/script.js', __FILE__ ), array( 'jquery', 'isotope-js' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'uw_directory_enqueue_scripts' );



function uw_directory_shortcode() {

    /* ---------- Search / Filter / View toggles ---------- */
    ob_start();
    ?>
    <div class="uw-directory">
    <div class="folklore-container">

    <!-- Filter Box -->
<div class="folklore-box">
  <div class="filter">
    <?php
    $departments = array();
    $query = new WP_Query(array(
      'post_type' => 'directory_entry',
      'posts_per_page' => -1,
    ));

    if ($query->have_posts()) :
      while ($query->have_posts()) :
        $query->the_post();
        $dept = get_field('department');
        if ($dept && !in_array($dept, $departments, true)) {
          $departments[] = trim($dept);
        }
      endwhile;
      wp_reset_postdata();
    endif;
    ?>

    <div class="dropdown custom-dropdown">
      <button id="dropdownMenuButton" class="custom-btn" data-bs-toggle="dropdown" aria-expanded="false">
        <span id="dropdown-label" class="label-text">Categories</span>
        <span class="arrow-block">&#9660;</span>
      </button>
      <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
        <?php foreach ($departments as $dept) :
          $slug = strtolower(str_replace(' ', '-', $dept)); ?>
          <li>
            <a class="dropdown-item" href="#" data-value="<?php echo esc_html($dept); ?>" data-filter=".<?php echo esc_attr($slug); ?>">
              <?php echo esc_html($dept); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<!-- Search Box -->
<div class="folklore-box search">
  <section aria-label="Search" style="width: 100%;">
    <form class="searchbox">
      <div>
        <input type="text" id="s" placeholder="Search for name, team, role" autocomplete="off" />
        <button type="submit" id="searchsubmit"></button>
      </div>
    </form>
  </section>
</div>
<!-- View Toggle Box -->
<div class="folklore-box view-toggle">
    <div class="view-dropdown" style="display:flex; flex-wrap: wrap;">
        <button class="view-btn tab-view tab-button active" type="button" id="gridViewBtn" data-tab="tab-one" >
            <i class="fa-solid fa-border-all" style="margin-right:10px;"></i> Grid
        </button>
        <button class="view-btn tab-view tab-button" type="button" id="listViewBtn" data-tab="tab-two">
       <i class="fa-solid fa-table-list" style="margin-right:10px;"></i>List
        </button>
    </div>
</div>

</div>



        <?php
        /* ----------  Tabs ---------- */
        echo '<div id="tab-one" class="tab-content" style="display:block;"><div id="directory-container">';

        $query       = new WP_Query( array(
            'post_type'      => 'directory_entry',
            'posts_per_page' => -1,
        ) );
        $table_rows  = '';

        if ( $query->have_posts() ) :
            while ( $query->have_posts() ) :
                $query->the_post();

                $first  = get_field( 'first_name' );
                $last   = get_field( 'last_name' );
                $email  = get_field( 'email' );
                $pic    = get_field( 'image' );
                $dept   = get_field( 'department' );
                $title  = get_field( 'title' );
                $bio    = get_field( 'bio' );
                $d_slug = strtolower( str_replace( ' ', '-', $dept ) );

                /* ----- Grid Tab ----- */
                ?>
                <div class="uw-card <?php echo esc_attr( $d_slug ); ?>"
                     data-name="<?php echo esc_attr( "$first $last" ); ?>"
                     data-email="<?php echo esc_attr( $email ); ?>"
                     data-department="<?php echo esc_attr( $d_slug ); ?>">
                    <img src="<?php echo esc_url( $pic['url'] ); ?>" alt="Profile Image" class="uw-card-img"/>
                    <div class="uw-card-text"><span>
                        <h6 style="font-weight:bold;"><?php echo esc_html( "$first $last" ); ?></h6>
                        <div class="udub-slant-divider white"><span></span></div>
                        <p style="color:white;font-weight:bold;font-size:16px;"><?php echo esc_html( $title ); ?></p>
                        <p style="color:white;font-size:16px;"><?php echo esc_html( $dept ); ?></p>
                        <p style="color:white;font-weight:bold;font-size:16px;"><?php echo esc_html( $email ); ?></p>
                        <p class="button">
                            <button class="btn btn-sm secondary light-gold open-profile-modal"
                                    data-name="<?php echo esc_attr( "$first $last" ); ?>"
                                    data-title="<?php echo esc_attr( $title ); ?>"
                                    data-department="<?php echo esc_attr( $dept ); ?>"
                                    data-email="<?php echo esc_attr( $email ); ?>"
                                    data-bio="<?php echo esc_attr( $bio ); ?>"
                                    data-img="<?php echo esc_url( $pic['url'] ); ?>">
                                <span>View Profile</span>
                            </button>
                        </p>
                    </span></div>
                </div>
                <?php
                /* ----- List View  ----- */
                $table_rows .= sprintf(
                    '<tr class="open-profile-modal" data-name="%1$s" data-title="%2$s" data-email="%3$s" data-department="%4$s" data-bio="%5$s" data-img="%6$s" data-department-slug="%7$s">
                        <td><img src="%6$s" alt="Profile"></td>
                        <td><strong>%1$s</strong></td>
                        <td>%2$s</td>
                        <td>%4$s</td>
                        <td><a href="mailto:%3$s">%3$s</a></td>
                    </tr>',
                    esc_attr( "$first $last" ),
                    esc_attr( $title ),
                    esc_attr( $email ),
                    esc_attr( $dept ),
                    esc_attr( $bio ),
                    esc_url( $pic['url'] ),
                    esc_attr( $d_slug )
                );
            endwhile;
            wp_reset_postdata();
        endif;

        echo '</div></div>'; 

        ?>
        <div id="tab-two" class="tab-content" style="display:none;">
            <div id="directory-table-wrapper">
                <table class="directory-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody><?php echo $table_rows; ?></tbody>
                </table>
            </div>
        </div>

        <!-- Bio modal -->
        <div id="profile-modal" class="uw-modal" style="display:none;">
            <div class="uw-modal-content">
                <span class="uw-modal-close">&times;</span>
                <div class="uw-modal-flex">
                    <div class="uw-modal-img-col">
                        <img id="modal-img" src="" alt="Profile Image"/>
                    </div>
                    <div class="uw-modal-text-col">
                        <h2 id="modal-name"></h2>
                        <p id="modal-title" class="bold"></p>
                        <p id="modal-department" class="light-text"></p>
                        <p id="modal-bio"></p>
                    </div>
                </div>
                <div class="uw-modal-footer">
                    <h4 id="modal-connect-header"></h4>
                    <p id="modal-email"></p>
                    <p id="modal-links"></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'uw_directory', 'uw_directory_shortcode' );
