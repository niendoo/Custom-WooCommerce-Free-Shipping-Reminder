<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Plugin Name: Custom Free Shipping Reminder
 * Description: A custom WooCommerce plugin to automatically apply free shipping when users spend a certain amount.
 * Version: 1.4
 * Author: Abdul Rauf
 *  * Author URI: https://guruseoservices.com/about-us/
 * Text Domain: custom-free-shipping-reminder
 */


// Check for direct access
if (!defined('ABSPATH')) {
    exit;
}

// Initialize plugin settings
function custom_free_shipping_init_settings() {
    // Register the settings
    register_setting('custom_free_shipping_settings_group', 'custom_free_shipping_min_amount', 'absint');
    register_setting('custom_free_shipping_settings_group', 'custom_free_shipping_message', 'sanitize_text_field');
    register_setting('custom_free_shipping_settings_group', 'custom_free_shipping_background_color', 'sanitize_hex_color');
    register_setting('custom_free_shipping_settings_group', 'custom_free_shipping_text_color', 'sanitize_hex_color');
    
    // Add settings section for reminder settings
    add_settings_section('custom_free_shipping_settings_section', 'Free Shipping Reminder Settings', 'custom_free_shipping_section_callback', 'custom-free-shipping-reminder');
    
    // Add settings fields for reminder settings
    add_settings_field('custom_free_shipping_min_amount_field', 'Minimum Amount for Free Shipping', 'custom_free_shipping_min_amount_field_callback', 'custom-free-shipping-reminder', 'custom_free_shipping_settings_section');
    add_settings_field('custom_free_shipping_message_field', 'Custom Message', 'custom_free_shipping_message_field_callback', 'custom-free-shipping-reminder', 'custom_free_shipping_settings_section');
    
    // Add settings section for styling options
    add_settings_section('custom_free_shipping_styles_section', 'Styling Options', 'custom_free_shipping_styles_section_callback', 'custom-free-shipping-reminder');
    
    // Add settings fields for styling options
    add_settings_field('custom_free_shipping_background_color_field', 'Background Color', 'custom_free_shipping_background_color_field_callback', 'custom-free-shipping-reminder', 'custom_free_shipping_styles_section');
    add_settings_field('custom_free_shipping_text_color_field', 'Text Color', 'custom_free_shipping_text_color_field_callback', 'custom-free-shipping-reminder', 'custom_free_shipping_styles_section');
}
add_action('admin_init', 'custom_free_shipping_init_settings');

// Section callback function for reminder settings
function custom_free_shipping_section_callback() {
    echo '<p>Configure the settings for your Free Shipping Reminder plugin.</p>';
}

// Minimum amount field callback function
function custom_free_shipping_min_amount_field_callback() {
    $min_amount = get_option('custom_free_shipping_min_amount', 50); // Default value is 50
    echo '<input type="number" name="custom_free_shipping_min_amount" id="custom_free_shipping_min_amount" value="' . esc_attr($min_amount) . '" />';
}

// Message field callback function
function custom_free_shipping_message_field_callback() {
    $message = get_option('custom_free_shipping_message', 'Spend %s more to qualify for free shipping!'); // Default message
    echo '<input type="text" name="custom_free_shipping_message" id="custom_free_shipping_message" value="' . esc_attr($message) . '" />';
}

// Section callback function for styling options
function custom_free_shipping_styles_section_callback() {
    echo '<p>Configure the styling options for the free shipping reminder.</p>';
}

// Background color field callback function
function custom_free_shipping_background_color_field_callback() {
    $background_color = get_option('custom_free_shipping_background_color', '#ffcc00');
    echo '<input type="color" name="custom_free_shipping_background_color" value="' . esc_attr($background_color) . '" />';
}

// Text color field callback function
function custom_free_shipping_text_color_field_callback() {
    $text_color = get_option('custom_free_shipping_text_color', '#000');
    echo '<input type="color" name="custom_free_shipping_text_color" value="' . esc_attr($text_color) . '" />';
}

// Hook into the WooCommerce cart page and display the message
function custom_free_shipping_reminder() {
    $min_amount = get_option('custom_free_shipping_min_amount', 50); // Get the minimum amount from settings
    $message = get_option('custom_free_shipping_message', 'Spend %s more to qualify for free shipping!'); // Get the message from settings
    
    // Get the cart subtotal
    $cart_subtotal = WC()->cart->get_subtotal();

    // Calculate the remaining amount for free shipping
    $remaining_amount = $min_amount - $cart_subtotal;

    // Check if the cart subtotal is below the minimum amount
    if ($remaining_amount <= 0) {
        // Apply free shipping
        WC()->shipping()->reset_shipping();
        WC()->shipping()->calculate_shipping();
        WC()->cart->calculate_totals();

        // Output a success message (escaped)
        echo '<div class="custom-free-shipping-message success">';
        echo 'Congratulations! You qualify for free shipping!';
        echo '</div>';
    } else {
        // Display the reminder message (escaped)
        echo '<div class="custom-free-shipping-message">';
        printf(esc_html($message), wc_price($remaining_amount));
        echo '</div>';
    }
}

// Display the reminder message under every sub-total
function custom_free_shipping_display_message() {
    custom_free_shipping_reminder();
}

add_action('woocommerce_cart_totals_before_order_total', 'custom_free_shipping_display_message');

// Define a shortcode for displaying the free shipping reminder
function custom_free_shipping_shortcode() {
    ob_start(); // Start output buffering
    custom_free_shipping_reminder(); // Display the reminder message
    $output = ob_get_clean(); // Get the buffered output and clean the buffer
    return $output; // Return the output
}

// Register the shortcode with the name "free_shipping_reminder"
add_shortcode('free_shipping_reminder', 'custom_free_shipping_shortcode');

// Declare WordPress support
function custom_free_shipping_declare_support() {
    add_theme_support('woocommerce'); // Declare WooCommerce support
    load_plugin_textdomain('custom-free-shipping-reminder', false, dirname(plugin_basename(__FILE__)) . '/languages/'); // Load translations
}
add_action('after_setup_theme', 'custom_free_shipping_declare_support');

// Settings page callback function
function custom_free_shipping_settings_page() {
    ?>
    <div class="wrap">
        <h2>Free Shipping Reminder Settings</h2>
        <?php
            if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
            }
        ?>
        <form method="post" action="options.php">
            <?php
                settings_fields('custom_free_shipping_settings_group');
                do_settings_sections('custom-free-shipping-reminder');
                submit_button();
            ?>
        </form>
        <div class="shortcode-instructions">
            <h3>Shortcode Instructions</h3>
            <p>Add the following shortcode to your posts or pages to display the free shipping reminder:</p>
            <code>[free_shipping_reminder]</code>
        </div>
    </div>
    <?php
}

// Style the reminder message
function custom_free_shipping_styles() {
    $background_color = get_option('custom_free_shipping_background_color', '#ffcc00');
    $text_color = get_option('custom_free_shipping_text_color', '#000');
    
    echo '<style>
        .custom-free-shipping-message {
            background-color: ' . $background_color . ';
            color: ' . $text_color . ';
            padding: 10px;
            margin-top: 10px;
            text-align: center;
        }
        .custom-free-shipping-message.success {
            background-color: #66bb6a;
        }
    </style>';
}
// Add the settings page to the WooCommerce menu
function custom_free_shipping_add_menu() {
    add_menu_page(
        'Free Shipping Reminder Settings',
        'Free Shipping Reminder',
        'manage_options',
        'custom-free-shipping-reminder',
        'custom_free_shipping_settings_page',
        'dashicons-cart',
        90
    );
}

add_action('admin_menu', 'custom_free_shipping_add_menu');


add_action('wp_head', 'custom_free_shipping_styles');
?>