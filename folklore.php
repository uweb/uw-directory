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
add_action( 'admin_init', 'uw_directory_check_acf' );
add_action( 'init', 'uw_register_department_taxonomy', 0 );
function uw_register_department_taxonomy() {
  register_taxonomy( 'department', 
    [ 'directory_entry' ],    
    [
      'labels'            => [
        'name'          => 'Departments',
        'singular_name' => 'Department',
      ],
      'hierarchical'      => true,    
      'show_ui'           => true,    
      'show_admin_column' => true,   
      'show_in_rest'      => true,    
      'rewrite'           => [ 'slug' => 'department' ],
    ]
  );
}

/**
		 * Check to see if ACF Pro, STM, or ACF is active. If ACF not Pro is active, show error. If none are active, show error.
		 */
		 function uw_directory_check_acf() {
			// list of acceptable plugins to get ACF Pro
			$all_plugins = [
				'advanced-custom-fields-pro/acf.php',
				'uw-storytelling-modules/class-uw-storytelling-modules.php',
				'uw-storytelling-modules-master/class-uw-storytelling-modules.php',
				'uw-storytelling-modules-develop/class-uw-storytelling-modules.php',
				'uw-storytelling-modules-main/class-uw-storytelling-modules.php' // this one may exist in the future if we change from master to main.
			];

			if ( is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
				?>
				<div class="notice notice-error">
					<p><?php esc_html_e( "UW Folklore requires Advanced Custom Fields Pro or UW Storytelling Modules. It looks like you're using Advanced Custom Fields (not pro). Please deactivate Advanced Custom Fields and activate Advanced Custom Fields Pro or Storytelling Modules instead.", 'uw-directory' ); ?></p>
				</div>
				<?php
				return;
			}

			foreach ( $all_plugins as $plugin ) {
				// if any of these plugins are active, then we are all set.
				if ( is_plugin_active( $plugin ) ) {
					// all good to go!
					return;
				}
			}

			// if we get here, we're out of checks and need either ACF Pro or STM activated.
			?>
				<div class="notice notice-error">
					<p><?php esc_html_e( "UW Folklore requires Advanced Custom Fields Pro or UW Storytelling Modules. Please activate Advanced Custom Fields Pro or UW Storytelling Modules.", 'uw-directory' ); ?></p>
				</div>
				<?php
				return;
		}

add_action( 'init', 'register_directory_post_type' );
// save and load acf json for this plugin.
add_filter( 'acf/settings/save_json/key=group_67ae559c84ef4', 'uw_directory_acf_json_save_point'  );
add_filter( 'acf/settings/load_json','uw_directory_acf_json_load_point');

function uw_directory_enqueue_scripts() {
    // Vendor libs
    wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' );
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css' );

    wp_enqueue_script( 'isotope-js', 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );

    // Plugin assets
    wp_enqueue_style( 'uw-directory-style', plugins_url( 'folklore.css', __FILE__ ) );
    wp_enqueue_script( 'uw-directory-script', plugins_url( '/script.js', __FILE__ ), array( 'jquery', 'isotope-js' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'uw_directory_enqueue_scripts' );

/**
		 * Use Local JSON to store data for ACF.
		 * Save point.
		 */
		 function uw_directory_acf_json_save_point( $path ) {
			// update path.
			$path = plugin_dir_path( __FILE__ ) . '/acf-json';

			// return.
			return $path;
		}

		/**
		 * Use Local JSON to load saved data for ACF.
		 * Load point.
		 */
		 function uw_directory_acf_json_load_point( $paths ) {
			// remove original path (optional).
			//unset( $paths[0] );

			// append path.
			$paths[] = plugin_dir_path( __FILE__ ) . '/acf-json';

			// return.
			return $paths;
		}

    function uw_directory_shortcode() {
      $post_id     = get_queried_object_id();
      $sidebar_val = get_post_meta( $post_id, 'sidebar', true );
      $has_sidebar = empty( $sidebar_val );
      // DEBUG: output values into your page source
echo '<!-- sidebar_val="' . esc_html( $sidebar_val ) . '" | has_sidebar=' . ( $has_sidebar ? 'true' : 'false' ) . ' -->';

      ob_start();
      ?>
<div class="uw-directory<?php echo $has_sidebar ? ' has-sidebar' : ''; ?>">
<div class="folklore-container">

    <!-- Filter Box -->
<div class="folklore-box">
<div class="folklore-inner">
<label class="section-label" for="dropdownMenuButton">
  Categories filter dropdown   <br><small style="font-weight: normal; color: #666;">Sorts by department</small>

</label>

  <div class="filter">
    <?php
     $terms = get_terms([
        'taxonomy'   => 'department',
        'hide_empty' => true,
      ]);
  
      $departments = [];
      if ( ! is_wp_error($terms) ) {
        foreach ( $terms as $term ) {
          $departments[ $term->slug ] = $term->name;
        }
      }
  
    ?>

    <div class="dropdown custom-dropdown">
      <button id="dropdownMenuButton" class="custom-btn" data-bs-toggle="dropdown" aria-expanded="false">
        <span id="dropdown-label" class="label-text">All categories</span>
        <span class="arrow-block">&#9660;</span>
      </button>
      <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
      <li>
        <a class="dropdown-item" href="#" data-value="All categories" data-filter="*">All categories</a>
      </li>
<?php foreach ( $departments as $slug => $name ) : ?>
      <li>
        <a
          class="dropdown-item"
          href="#"
          data-value="<?php echo esc_attr( $name ); ?>"
          data-filter=".<?php echo esc_attr( $slug ); ?>"
        >
          <?php echo esc_html( $name ); ?>
        </a>
      </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  </div>
</div>

<!-- Search Box -->
<div class="folklore-box search">
<div class="folklore-inner">
<label class="section-label" for="dropdownMenuButton">
  Search for name, team or role <br><small style="font-weight: normal; color: #666;">Results will update as you type</small>
</label>
  <section aria-label="Search" style="width: 100%;">
    <form class="searchbox">
      <div>
        <input type="text" id="s" placeholder="Search for name, team, role" autocomplete="off" />
        <button type="submit" id="searchsubmit"></button>
      </div>
    </form>
  </section>

        </div>
</div>
<!-- View Toggle Box -->
<div class="folklore-box view-toggle">
    <!-- <label class="section-label toggle-view" for="dropdownMenuButton">Filter</label> -->

    <div class="toggle-view">
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
        echo '<div id="tab-one" class="tab-content" style="display:block;"><div id="directory-container" aria-live="polite" aria-atomic="true">';
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
                $default_img = plugins_url( 'assets/dubs.jpg', __FILE__ );
                $terms = get_the_terms( get_the_ID(), 'department' );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $term   = array_shift( $terms );
                    $dept   = $term->name;
                    $d_slug = $term->slug;
                } else {
                    $dept   = '';
                    $d_slug = '';
                }
                                
                $title  = get_field( 'title' );
                $bio    = get_field( 'bio' );
                $img_url = ( $pic && ! empty( $pic['url'] ) )
                ? esc_url( $pic['url'] )
                : esc_url( $default_img );
                /* ----- Grid Tab ----- */
                ?>
                <div class="uw-card <?php echo esc_attr( $d_slug ); ?>"
                     data-name="<?php echo esc_attr( "$first $last" ); ?>"
                     data-email="<?php echo esc_attr( $email ); ?>"
                     data-department="<?php echo esc_attr( $d_slug ); ?>">
                     <img src="<?php echo $img_url; ?>" alt="Profile Image" class="uw-card-img"/>
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
                                    data-img="<?php echo $img_url; ?>">
                                <span>View Profile</span>
                            </button>
                        </p>
                    </span></div>
                </div>
                <?php
                /* ----- List View  ----- */
                $table_rows .= sprintf(
                  '<tr class="open-profile-modal" role="button" tabindex="0" aria-label="View %1$s’s profile" 
                       data-name="%1$s" 
                       data-title="%2$s" 
                       data-email="%3$s" 
                       data-department="%4$s" 
                       data-bio="%5$s" 
                       data-img="%6$s" 
                       data-department-slug="%7$s">
                      <td><img src="%6$s" alt="Profile of %1$s"></td>
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
                    esc_url( $img_url ),
                    esc_attr( $d_slug )
                );
            endwhile;
            wp_reset_postdata();
        endif;

        echo '</div></div>'; 

        ?>
        <div id="tab-two" class="tab-content" style="display:none;">
            <div id="directory-table-wrapper">
            <label class="section-label">Click a row to view the full profile</label>

                <table class="directory-table" >
                <caption class="screen-reader-text">Click a row to view the full profile</caption>
                    <thead>
                        <tr role="button">
                            <th></th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody aria-live="polite" aria-atomic="false"><?php echo $table_rows; ?></tbody>
                  </table>
            </div>
        </div>
        <div id="no-results-message" style="display:none; text-align:center; margin:2rem 0;">
            <p>No results found.</p>
        </div>

        <!-- Bio modal -->
        <div id="profile-modal" class="uw-modal" style="display:none;">
            <div class="uw-modal-content">
                <!-- <span class="uw-modal-close">&times;</span> -->
              <button
                  type="button"
                  class="uw-modal-close"
                  role="button"
                  tabindex="0"
                  aria-label="Close profile modal">
                &times;
              </button>
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

/* add_filter( 'document_title_parts', 'folklore_override_title' );
function folklore_override_title( $title ) {
    if ( is_page() && has_shortcode( get_post()->post_content, 'uw_directory' ) ) {
        $title['title'] = 'Folklore people directory';
    }
    return $title;
}
 */