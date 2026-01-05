# UW Directory (AKA Folklore) Plugin

Plugin to displays a searchable, filterable directory of people with grid/list views and profile modals.



## Features

- **Custom Post Type:**  
  `directory_entry` for managing people profiles.

- **Custom Taxonomy:**  
  Departments for categorizing entries.

- **Front-End Directory:**
  - Filter by department or categories.
  - Live search by name, department, title and email.
  - Toggle between grid and list views.
  - Modal popups for detailed profiles.

- **Accessibility:**  
  Built-in ARIA labels and keyboard navigation.  
  Responsive layouts for mobile, tablet, and desktop.



## Installation

1. **Upload the Plugin**
   - Upload the plugin folder to:  
     `/wp-content/plugins/uw-directory/`

2. **Activate the Plugin**
   - In your WordPress admin dashboard, go to **Plugins** and activate **Folklore Plugin**.

3. **Install Required Plugins**
   - Make sure **UW Storytelling Modules** *or* **Advanced Custom Fields (ACF)** is active for custom field functionality.



## Usage

1. **Assign Departments**
   - Use the **Departments** taxonomy to organize entries.
   - Add departments via ACF in WP dashboard.

2. **Add Directory Entries**
   - Go to **Directory > Add New Entry** in the WordPress admin.
   - Fill out the **required fields**: `first name`, `last name`, `title`, `department`  
     **Optional fields**: `email`, `image`, `bio`, `pronouns`, `website`, `LinkedIn`

3. **Display the Directory**
   - Add either of the following shortcodes to any page or post:

     ```php
     [uw_directory]
     [folklore]
     ```

   - Both shortcodes render the directory.



## Developer Customization

- **Fields:**  
  Extend or modify fields via ACF using the field group `group_67ae559c84ef4`.

- **Styling:**  
 Modify `folklore.scss` to change the look and feel, or enqueue your own stylesheet to override the defaults. After editing the styles, run `npx gulp` to rebuild the CSS before refreshing the browser.


- **Scripts:**  
  Custom JavaScript lives in `script.js`. You can extend or override functionality by enqueuing additional scripts.

- **Assets:**  
  Uses CDN-hosted Bootstrap, FontAwesome, Bootstrap Icons, and Isotope.js.  
  Swap these out with local assets if needed.



## Notes

- The plugin includes accessibility and responsive design features, but you should still test it against your theme.
- Keep bios under **1000 characters**, including spaces to prevent layout issues.
- The Pro version of ACF is not required for this plugin.







