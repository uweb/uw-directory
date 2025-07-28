<?php
/*
Plugin Name: Folklore Plugin
Description: Displays UW directory with filtering, searching, and grid/list views.
Version: 1.0
Author: UW
*/

if (!defined("ABSPATH")) {
    exit();
}

function register_directory_post_type()
{
    $args = [
        "labels" => [
            "name" => "Directory Entries",
            "singular_name" => "Directory Entry",
            "add_new" => "Add New Entry",
            "add_new_item" => "Add New Directory Entry",
            "edit_item" => "Edit Directory Entry",
            "new_item" => "New Directory Entry",
            "view_item" => "View Directory Entry",
            "search_items" => "Search Directory Entries",
            "not_found" => "No entries found",
            "not_found_in_trash" => "No entries found in Trash",
            "all_items" => "All Directory Entries",
            "menu_name" => "Directory",
        ],
        "public" => true,
        "has_archive" => true,
        "menu_icon" => "dashicons-id",
        "supports" => ["title", "editor"],
        "show_in_rest" => true,
    ];

    register_post_type("directory_entry", $args);
}
add_action("admin_init", "uw_directory_check_acf");
add_action("init", "uw_register_department_taxonomy", 0);
function uw_register_department_taxonomy()
{
    register_taxonomy(
        "department",
        ["directory_entry"],
        [
            "labels" => [
                "name" => "Departments",
                "singular_name" => "Department",
            ],
            "hierarchical" => true,
            "show_ui" => true,
            "meta_box_cb" => false,
            "show_admin_column" => true,
            "show_in_rest" => true,
            "rewrite" => ["slug" => "department"],
        ]
    );
}

/**
 * Check to see if ACF Pro, STM, or ACF is active. If ACF not Pro is active, show error. If none are active, show error.
 */
function uw_directory_check_acf()
{
    // list of acceptable plugins to get ACF Pro
    $all_plugins = [
        "advanced-custom-fields-pro/acf.php",
        "uw-storytelling-modules/class-uw-storytelling-modules.php",
        "uw-storytelling-modules-master/class-uw-storytelling-modules.php",
        "uw-storytelling-modules-develop/class-uw-storytelling-modules.php",
        "uw-storytelling-modules-main/class-uw-storytelling-modules.php", // this one may exist in the future if we change from master to main.
    ];

    if (is_plugin_active("advanced-custom-fields/acf.php")) { ?>
				<div class="notice notice-error">
					<p><?php esc_html_e(
         "UW Folklore requires Advanced Custom Fields Pro or UW Storytelling Modules. It looks like you're using Advanced Custom Fields (not pro). Please deactivate Advanced Custom Fields and activate Advanced Custom Fields Pro or Storytelling Modules instead.",
         "uw-directory"
     ); ?></p>
				</div>
				<?php return;}

    foreach ($all_plugins as $plugin) {
        // if any of these plugins are active, then we are all set.
        if (is_plugin_active($plugin)) {
            // all good to go!
            return;
        }
    }
    // if we get here, we're out of checks and need either ACF Pro or STM activated.
    ?>
				<div class="notice notice-error">
					<p><?php esc_html_e(
         "UW Folklore requires Advanced Custom Fields Pro or UW Storytelling Modules. Please activate Advanced Custom Fields Pro or UW Storytelling Modules.",
         "uw-directory"
     ); ?></p>
				</div>
				<?php return;
}

add_action("init", "register_directory_post_type");
// save and load acf json for this plugin.
add_filter(
    "acf/settings/save_json/key=group_67ae559c84ef4",
    "uw_directory_acf_json_save_point"
);
add_filter("acf/settings/load_json", "uw_directory_acf_json_load_point");

function uw_directory_enqueue_scripts()
{
    // Vendor libs
    wp_enqueue_style(
        "bootstrap-css",
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    );
    wp_enqueue_script(
        "bootstrap-js",
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js",
        ["jquery"],
        null,
        true
    );
    wp_enqueue_style(
        "bootstrap-icons",
        "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    );

    wp_enqueue_script(
        "isotope-js",
        "https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js",
        ["jquery"],
        null,
        true
    );
    wp_enqueue_style(
        "font-awesome",
        "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css",
        [],
        "6.0.0"
    );

    // Plugin assets
    wp_enqueue_style(
        "uw-directory-style",
        plugins_url("folklore.css", __FILE__)
    );
    wp_enqueue_script(
        "uw-directory-script",
        plugins_url("/script.js", __FILE__),
        ["jquery", 'bootstrap-js', "isotope-js"],
        null,
        true
    );
}
add_action("wp_enqueue_scripts", "uw_directory_enqueue_scripts");

/**
 * Use Local JSON to store data for ACF.
 * Save point.
 */
function uw_directory_acf_json_save_point($path)
{
    // update path.
    $path = plugin_dir_path(__FILE__) . "/acf-json";

    // return.
    return $path;
}

/**
 * Use Local JSON to load saved data for ACF.
 * Load point.
 */
function uw_directory_acf_json_load_point($paths)
{
    // remove original path (optional).
    //unset( $paths[0] );

    // append path.
    $paths[] = plugin_dir_path(__FILE__) . "/acf-json";

    // return.
    return $paths;
}

function uw_directory_shortcode()
{
    $post_id = get_queried_object_id();
    $sidebar_val = get_post_meta($post_id, "sidebar", true);
    $has_sidebar = empty($sidebar_val);
    // DEBUG: output values into your page source
    echo '<!-- sidebar_val="' .
        esc_html($sidebar_val) .
        '" | has_sidebar=' .
        ($has_sidebar ? "true" : "false") .
        " -->";

    ob_start();
    ?>
<div class="uw-directory<?php echo $has_sidebar ? " has-sidebar" : ""; ?>">
<div class="folklore-container">

    <!-- Filter Box -->
<div class="folklore-box">
<div class="folklore-inner">
<p class="section-label" for="dropdownMenuButton">
  Categories filter  <br><small>Select from the dropdown</small>

</p>

  <div class="filter">
    <?php
    $terms = get_terms([
        "taxonomy" => "department",
        "hide_empty" => true,
    ]);

    $departments = [];
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $departments[$term->slug] = $term->name;
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
      <?php foreach ($departments as $slug => $name): ?>
  <li><a class="dropdown-item" href="#" data-value="<?php echo esc_attr($name); ?>" data-filter=".<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></a></li>
<?php endforeach; ?>
      </ul>
    </div>
  </div>
  </div>
</div>

<!-- Search Box -->
<div class="folklore-box search">
<div class="folklore-inner">
<p class="section-label" for="dropdownMenuButton">
  Search for name, team or role <br><small>Results will update as you type</small>
      </p>
  <section aria-label="Search" style="width: 100%;">
  <form class="searchbox">
    <div>
        <input type="text" id="searchbar" placeholder="Search for name, team, role" autocomplete="off" />
        <button type="submit" id="searchsubmit" disabled></button>
    </div>
</form>

  </section>

        </div>
</div>
<div class="folklore-box ">
<div class="folklore-inner">

  <div>
        <button type="button" class="clear-filters view-btn tab-view tab-button active">
            <i class="fa-solid fa-xmark fa-lg" ></i>Clear all filters
        </button>
    </div>

        </div>
</div>


<!-- View Toggle Box -->
<div class="folklore-box view-toggle">

    <div class="toggle-view">
        <button class="view-btn tab-view tab-button active" type="button" id="gridViewBtn" data-tab="tab-one" >
            <i class="fa-solid fa-border-all" ></i> Grid
        </button>
        <button class="view-btn tab-view tab-button" type="button" id="listViewBtn" data-tab="tab-two">
       <i class="fa-solid fa-table-list"></i>List
        </button>
    </div>
</div>

</div>
<div style="display: flex; justify-content: center; margin-bottom:12px">
  <p class="section-label" id="results-count" aria-live="polite" aria-atomic="true">
  </p>
</div>


        <?php
        /* ----------  Grid/list views ---------- */
        echo '<div id="tab-one" class="tab-content" style="display:block;"><div id="directory-container" aria-live="polite" aria-atomic="true">';
        $query = new WP_Query([
            "post_type" => "directory_entry",
            "posts_per_page" => -1,
            "meta_key" => "last_name",       
            "orderby" => "meta_value",        
            "order" => "ASC",                 
        ]);
        $table_rows = "";

        if ($query->have_posts()):
            while ($query->have_posts()):

                $query->the_post();

                $first = get_field("first_name");
                $last = get_field("last_name");
                $email = get_field("email");
                $website = get_field("website");
                $pic = get_field("image");
                $default_img = plugins_url("assets/dubs.png", __FILE__);
                $terms = get_the_terms(get_the_ID(), "department");
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term = array_shift($terms);
                    $dept = $term->name;
                    $d_slug = $term->slug;
                } else {
                    $dept = "";
                    $d_slug = "";
                }

                $title = get_field("title");
                $bio = get_field("bio");
                
                $img_url =
                    $pic && !empty($pic["url"])
                        ? esc_url($pic["url"])
                        : esc_url($default_img);
                        $pronouns = get_field("pronouns");
                        $linkedin = get_field("linkedin");
                /* ----- Grid View ----- */
                ?>
                <div class="uw-card <?php echo esc_attr($d_slug); ?>"
                     data-name="<?php echo esc_attr("$first $last"); ?>"
                     
                     data-email="<?php echo esc_attr($email); ?>"
                     data-department="<?php echo esc_attr($d_slug); ?>">
                     <img src="<?php echo $img_url; ?>" alt="Profile Image" class="uw-card-img"/>
                    <div class="uw-card-text"><span>
                        <h2 class="h2 card-name"><?php echo esc_html(
                            "$first $last"
                        ); ?></h2>
                        <div class="udub-slant-divider white"><span></span></div>
                        
                       <p class="title"><?php echo esc_html(
                            $title
                        ); ?></p>
                 
                        <p class="department"><?php echo esc_html(
                            $dept
                        ); ?></p>
                        <p class="email"><?php echo esc_html(
                            $email
                        ); ?></p>
                        <p class="button">
                            <button class="btn btn-sm secondary light-gold open-profile-modal"
                                    data-name="<?php echo esc_attr(
                                        "$first $last"
                                    ); ?>"
                                    data-title="<?php echo esc_attr($title); ?>"
                                    data-department="<?php echo esc_attr(
                                        $dept
                                    ); ?>"
                                    data-email="<?php echo esc_attr($email); ?>"
                                     data-website="<?php echo esc_attr($website); ?>"
                                      data-pronouns="<?php echo esc_attr($pronouns); ?>"
        data-linkedin="<?php echo esc_attr($linkedin); ?>"
                                     
                                     <?php

$bio_html =  esc_attr( $bio );
?>
data-bio="<?php echo esc_attr( $bio_html ); ?>"  data-img="<?php echo $img_url; ?>">
                                <span>View Profile</span>
                            </button>
                        </p>
                    </span></div>
                </div>
                <?php /* ----- List View  ----- */
                $email_html = $email
                  ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
                  : '';   
$table_rows .= '<tr class="open-profile-modal" role="button" tabindex="0" aria-label="View ' . esc_attr("$first $last") . '’s profile"'
    . ' data-name="' . esc_attr("$first $last") . '"'
    . ' data-title="' . esc_attr($title) . '"'
    . ' data-email="' . esc_attr($email) . '"'
    . ' data-department="' . esc_attr($dept) . '"'
    . ' data-bio="' . esc_attr($bio) . '"'
    . ' data-img="' . esc_url($img_url) . '"'
    . ' data-department-slug="' . esc_attr($d_slug) . '"'
    . ' data-pronouns="' . esc_attr($pronouns) . '"'
    . ' data-linkedin="' . esc_attr($linkedin) . '"'
    . ' data-website="' . esc_url($website) . '">'
    . '<td><img src="' . esc_url($img_url) . '" alt="Profile of ' . esc_attr("$first $last") . '"></td>'
    . '<td><strong>' . esc_html("$first $last") . '</strong></td>'
    . '<td>' . esc_html($title) . '</td>'
    . '<td>' . esc_html($dept) . '</td>'
    . '<td>' . $email_html        . '</td>'  
    . '</tr>';
endwhile;
wp_reset_postdata();
endif;

echo "</div></div>";
?>

<div id="tab-two" class="tab-content" style="display:none;">
    <div id="directory-table-wrapper" class="table-responsive-sm">
        <label class="section-label table-instruction">Click a row to view the full profile</label>
        <table class="directory-table">
            <caption class="screen-reader-text">Click a row to view the full profile</caption>
            <thead class="table-headers">
                <tr role="button">
                    <th></th>
                    <th scope="col">Name</th>
                    <th scope="col">Role</th>
                    <th scope="col">Department</th>
                    <th scope="col">Email</th>
                </tr>
            </thead>
            <tbody aria-live="polite" aria-atomic="false"><?php echo $table_rows; ?></tbody>
        </table>
    </div>
</div>


        <!-- Bio modal -->     
<div id="profile-modal" class="uw-modal"style="display: none;"  tabindex="-1" >
  <div class="folklore-modal-content">
    <button type="button" class="folklore-modal-close" aria-label="Close">&times;</button>
    <div class="folklore-modal-body">
      <div class="folklore-modal-left">
        <img id="modal-img" src="" alt="Profile Image" class="modal-photo" />
        <div class="modal-contact horizontal-modal-footer" aria-hidden="false">

  <h3 class="h3">Connect</h3>
  <div class="contact-item">
    <i class="fa-solid fa-envelope"></i>
    <a class="modal-email" href="" target="_blank">
      <span class="email-text">Email</span>
    </a>
  </div>
  <div class="contact-item" id="linkedin-item" style="display: none;">
    <i class="fa-brands fa-linkedin"></i>
    <a class="modal-linkedin" href="" target="_blank">
      <span>LinkedIn</span>
    </a>
  </div>
  <div class="contact-item" id="website-item" style="display: none;">
    <i class="fa-solid fa-globe"></i>
    <a class="modal-website" href="" target="_blank">
      <span>Website</span>
    </a>
  </div>

</div>

      </div>
      <div class="folklore-modal-right">
        <h2 id="modal-name"></h2>
        <p id="modal-pronouns" class="modal-pronouns" style="margin-top: -0.5rem; color: #666;"></p>
        <p id="modal-title" class="modal-subtitle"></p>
        <p id="modal-department" class="modal-dept"></p>
        <div id="modal-bio" class="modal-bio"></div>
        <span id="bio-toggle" tabindex="0" role="button" class="see-more-link" hidden>See more</span>

      </div>
    </div>
<div class="modal-contact vertical-modal-footer" aria-hidden="true" style="display: none;">
    <h3 class="h3">Connect</h3>
    <?php if (!empty($email)) : ?>
        <div class="contact-item">
            <i class="fa-solid fa-envelope"></i>
            <a class="modal-email" href="mailto:<?php echo esc_attr($email); ?>" target="_blank">
                <span class="email-text">Email</span>
            </a>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($linkedin)) : ?>
        <div class="contact-item">
            <i class="fa-brands fa-linkedin"></i>
            <a class="modal-linkedin" href="https://linkedin.com/in/<?php echo esc_attr($linkedin); ?>" target="_blank">
                <span>LinkedIn</span>
            </a>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($website)) : ?>
        <div class="contact-item">
            <i class="fa-solid fa-globe"></i>
            <a class="modal-website" href="<?php echo esc_url($website); ?>" target="_blank">
                <span>Website</span>
            </a>
        </div>
    <?php endif; ?>
</div>
    </div>
    <?php return ob_get_clean();
}
add_shortcode("uw_directory", "uw_directory_shortcode");
add_shortcode("folklore", "uw_directory_shortcode");