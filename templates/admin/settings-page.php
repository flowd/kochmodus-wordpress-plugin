<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields('kochmodus_settings_group');
        do_settings_sections('kochmodus-settings');
        submit_button(__('Save Settings', 'flowd-kochmodus'));
        ?>
    </form>
</div>
