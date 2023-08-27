<!-- Menu and Title Section -->
<div class="plugin-menu-title-section">
  <h1 class="plugin-title-style">UserViewExpress</h1>
  <nav class="plugin-menu">
    <ul>
    <li>
  <a href="?page=user_card_layout&uvx_sort=alphabetical">
    <button type="button"><span>&#8645;</span> Sort Alphabetically</button>
  </a>
</li>
<li>
  <a href="<?php echo admin_url('admin.php?page=user_card_layout&view=login_events'); ?>">
    <button type="button"><span>&#128196;</span>View Login Events</button>
  </a>
</li>
<li>
  <a href="<?php echo admin_url('admin.php?page=user_card_layout'); ?>">
    <button type="button"><span>&#8635;</span>Refresh View</button>
  </a>
</li>

    </ul>
    
    <?php if (!isset($_GET['view']) || $_GET['view'] !== 'login_events'): ?>
    <!-- Search Form -->
    <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="page" value="user_card_layout">
        <input type="hidden" id="hidden-uvx-sort" name="uvx_sort" value="<?php echo isset($_GET['uvx_sort']) ? esc_attr($_GET['uvx_sort']) : ''; ?>">
        <script>
        function setSortOption(option) {
        document.getElementById('hidden-uvx-sort').value = option;
        document.querySelector('form').submit();
         }
        </script>

        <input type="text" name="search" placeholder="Search user by name" value="<?php echo esc_attr($search_query); ?>">
        <input type="submit" value="Search">
    </form>
      <!-- Pagination options for user -->

    
<form id="users-per-page-form">
    <label for="users-per-page">Users per page:</label>
    <select id="users-per-page" name="users_per_page" onchange="updateUsersPerPage()">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="50">50</option>
        <option value="100">100</option>
    </select>
</form>
<script>
  // capture the selected value and reload the page -->
function updateUsersPerPage() {
    const selectedValue = document.getElementById("users-per-page").value;
    const url = new URL(window.location.href);
    url.searchParams.set('users_per_page', selectedValue);
    window.location.href = url.toString();
}
</script>
    <?php endif; ?>
  </nav>


</div>

<!-- Logic PHP Starts Here -->
<?php

// Check if viewing login events is requested
if (isset($_GET['view']) && $_GET['view'] === 'login_events') {
    echo '<h1>User Login Events</h1>';

    foreach ($users as $user) {
        $login_events = get_user_meta($user->ID, 'login_events', true);
        if (!empty($login_events)) {
            
            echo '<div class="login-event-wrapper">';
            
            echo '<h2>' . esc_html($user->display_name) . '</h2>';
            
            foreach ($login_events as $event) {
                echo '<div class="login-event-card">';  // Start of card
                echo '<p>Time: ' . esc_html($event['time']) . '</p>';
                echo '<p>IP: ' . esc_html($event['ip']) . '</p>';
                echo '</div>';  // End of card
            }
            echo '</div>';
        }
    }

} else {

    $users = get_users();

    // Check if sorting is requested
if (isset($_GET['uvx_sort']) && $_GET['uvx_sort'] === 'alphabetical') {
    //  line "Applying alphabetical sort."; 
    usort($users, function ($a, $b) {
        return strcasecmp($a->display_name, $b->display_name);
    });
} else {
    //  line "Applying admin-first sort."; 
    usort($users, function($a, $b) {
        $a_is_admin = in_array('administrator', $a->roles);
        $b_is_admin = in_array('administrator', $b->roles);
        if ($a_is_admin && !$b_is_admin) {
            return -1;
        }
        if ($b_is_admin && !$a_is_admin) {
            return 1;
        }
        return 0;
    });
}

// Capture the SEARCH query
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Filter the users if a search query exists
if ($search_query) {
    $users = array_filter($users, function($user) use ($search_query) {
        return strpos(strtolower($user->display_name), strtolower($search_query)) !== false;
    });
}

// Create Pagination Variables
$total_users = count($users);
$users_per_page = isset($_GET['users_per_page']) ? intval($_GET['users_per_page']) : 10;
$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

// Slice users for the current page
$start_index = ($current_page - 1) * $users_per_page;
$paged_users = array_slice($users, $start_index, $users_per_page);


    //  User Card HTML Code
    ?>
    <div class="user-card-wrapper">
 
        <?php foreach($paged_users as $user):
            $user_roles = $user->roles; // Fetch the roles for this user
            $is_admin = in_array('administrator', $user_roles); // Check if one of the roles is 'administrator'
            $user_profile_link = get_edit_user_link($user->ID); // Get the edit profile link for this user
            $last_login = get_user_meta($user->ID, 'last_login', true);
        ?>
        <a href="<?php echo $user_profile_link; ?>" class="user-card-link <?php echo $is_admin ? 'admin' : ''; ?>">    
            <div class="user-card <?php echo $is_admin ? 'admin' : ''; ?>">
                <div class="user-avatar">
                    <?php echo get_avatar($user->ID, 64); ?>
                </div>
                <div class="user-info">
                    <h3><?php echo $user->display_name; ?></h3>
                    <?php if($is_admin): ?>
                        <button class="admin-button">&#9733; Admin</button>
                    <?php endif; ?>
                    <?php if(!$is_admin): ?> <!-- Add role for non-admin users -->
                        <p class="role-display"><span class="white-icon">&#128100;</span> Role: <?php echo ucwords(implode(', ', $user_roles)); ?></p>
                    <?php endif; ?>
                    <div class="user-profile-info">
                        <!-- Hover Edit user profile section -->
                    <div class="edit-user-icon">
                            <span class="edit-icon">&#x270E;</span> <!-- Pencil Unicode Character -->
                            <span>Edit User</span>
                    </div>
                        <p class="email-class">Email: <?php echo $user->user_email; ?></p>
                        <p>First: <?php echo $user->first_name; ?></p>
                        <p>Last: <?php echo $user->last_name; ?></p>
                    </div>
                    <!-- Capture Login Time -->
                    <?php
                        $last_login_time = get_user_meta($user->ID, 'last_login_time', true);
                        $formatted_last_login_time = $last_login_time ? date("F j, Y, g:i a", strtotime($last_login_time)) : 'Has not signed in.';
                    ?>
                    <div class="last-login-section">
                        Last Login: <?php echo $formatted_last_login_time; ?>
                    </div>
                </div>
            </div>
        </a>
        
        <?php endforeach; ?>
      
      
    </div>
      <!-- Pagination HTML Code Starts Here -->
<div class="pagination">
    <?php
    $total_pages = ceil($total_users / $users_per_page);
    for ($i = 1; $i <= $total_pages; $i++) {
        $class = ($i === $current_page) ? 'class="active"' : ''; // Highlight the current page
        echo "<a $class href='?page=user_card_layout&paged=$i'>Page $i</a> "; // Generate page links
    }
    ?>
</div>
<!-- Pagination Code Ends Here -->
    <?php
    
}
    
?>
