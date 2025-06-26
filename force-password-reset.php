<?php
/*
Plugin Name: WP Force Password Reset
Plugin URI: https://github.com/rynecallahan019/wp-force-password-reset
GitHub Plugin URI: https://github.com/rynecallahan019/wp-force-password-reset
Description: Adding a user field that when set to true, forces the user to reset their password the next time they log in.
Version: 1.8.1
Author: Callabridge
Author URI: https://callabridge.com/
*/

// Include the updater library
require_once dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';

// Set up the updater
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$wpfprUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/rynecallahan019/wp-force-password-reset/',
    __FILE__,
    'wp-force-password-reset'
);

// Set the branch that contains the stable release
$wpfprUpdateChecker->setBranch('main');

// Enable GitHub release asset updates
$wpfprUpdateChecker->getVcsApi()->enableReleaseAssets();

function create_force_password_reset_field() {
    if( function_exists('acf_add_local_field_group') ):

        acf_add_local_field_group(array(
            'key' => 'group_force_password_reset',
            'title' => 'WP Force Password Reset',
            'fields' => array(
                array(
                    'key' => 'field_force_password_reset',
                    'label' => 'WP Force Password Reset',
                    'name' => 'force_password_reset',
                    'type' => 'true_false',
                    'instructions' => 'Check to force the user to reset their password.',
                    'ui' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'user_form',
                        'operator' => '==',
                        'value' => 'all',
                    ),
                ),
            ),
        ));

    endif;
}
add_action('acf/init', 'create_force_password_reset_field');

// Add menu item under Settings
function frp_add_options_page() {
    add_options_page(
        'WP Force Reset Password Settings',
        'WP Force Reset Password',
        'manage_options',
        'force-reset-password',
        'frp_render_options_page'
    );
}
add_action('admin_menu', 'frp_add_options_page');

// Register settings
function frp_register_settings() {
    register_setting('frp_options', 'frp_enable_2fa');
    register_setting('frp_options', 'frp_modal_heading');
    register_setting('frp_options', 'frp_modal_description');
    register_setting('frp_options', 'frp_accent_color');
}
add_action('admin_init', 'frp_register_settings');

// Render the options page
function frp_render_options_page() {
    $accent_color = get_option('frp_accent_color', '#3b82f6');
    ?>
    <div class="wrap frp-admin-wrap">
        <h1 class="frp-main-title">WP Force Reset Password Settings</h1>
        
        <div class="frp-settings-container">
            <div class="frp-settings-main">
                <div class="frp-card">
                    <h2 class="frp-card-title">Configuration Settings</h2>
                    <form method="post" action="options.php" class="frp-form">
                        <?php
                        settings_fields('frp_options');
                        do_settings_sections('frp_options');
                        ?>
                        
                        <div class="frp-form-grid">
                            <div class="frp-form-group">
                                <label for="frp_modal_heading" class="frp-form-label">
                                    <span class="frp-label-text">Modal Heading</span>
                                    <span class="frp-label-description">The title displayed in the password reset modal</span>
                                </label>
                                <input type="text" 
                                       id="frp_modal_heading" 
                                       name="frp_modal_heading" 
                                       value="<?php echo esc_attr(get_option('frp_modal_heading', 'Reset Your Password')); ?>" 
                                       class="frp-form-input" 
                                       placeholder="Reset Your Password" />
                            </div>

                            <div class="frp-form-group">
                                <label for="frp_modal_description" class="frp-form-label">
                                    <span class="frp-label-text">Modal Description</span>
                                    <span class="frp-label-description">Additional text shown below the modal heading</span>
                                </label>
                                <textarea id="frp_modal_description" 
                                          name="frp_modal_description" 
                                          rows="3" 
                                          class="frp-form-textarea"
                                          placeholder="Enter optional description text..."><?php echo esc_textarea(get_option('frp_modal_description', '')); ?></textarea>
                            </div>

                            <div class="frp-form-group">
                                <label for="frp_accent_color" class="frp-form-label">
                                    <span class="frp-label-text">Accent Color</span>
                                    <span class="frp-label-description">Primary color for buttons and form elements</span>
                                </label>
                                <div class="frp-color-input-wrapper">
                                    <input type="color" 
                                           id="frp_accent_color" 
                                           name="frp_accent_color" 
                                           value="<?php echo esc_attr($accent_color); ?>" 
                                           class="frp-color-input" />
                                    <input type="text" 
                                           value="<?php echo esc_attr($accent_color); ?>" 
                                           class="frp-color-text" 
                                           readonly />
                                </div>
                            </div>

                            <div class="frp-form-group frp-checkbox-group">
                                <label for="frp_enable_2fa" class="frp-checkbox-label">
                                    <input type="checkbox" 
                                           id="frp_enable_2fa" 
                                           name="frp_enable_2fa" 
                                           value="1" 
                                           <?php checked(1, get_option('frp_enable_2fa'), true); ?> 
                                           class="frp-checkbox-input" />
                                    <span class="frp-checkbox-custom"></span>
                                    <div class="frp-checkbox-content">
                                        <span class="frp-checkbox-title">Enable Two-Factor Authentication</span>
                                        <span class="frp-checkbox-description">Require email verification before password reset</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="frp-form-actions">
                            <?php submit_button('Save Settings', 'primary frp-submit-btn', 'submit', false); ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="frp-settings-sidebar">
                <div class="frp-card">
                    <h2 class="frp-card-title">About This Plugin</h2>
                    <p class="frp-card-text">WP Force Reset Password enables administrators to require specific users to reset their passwords upon their next login. This feature enhances security by allowing you to enforce password changes when necessary.</p>
                    
                    <h3 class="frp-card-subtitle">How It Works</h3>
                    <ol class="frp-ordered-list">
                        <li>Navigate to the user's profile in the WordPress admin area.</li>
                        <li>Locate the "WP Force Reset Password" field in the user settings.</li>
                        <li>Toggle the option to enable the password reset requirement.</li>
                        <li>The next time the user logs in, they will be prompted to reset their password.</li>
                    </ol>
                </div>

                <div class="frp-card">
                    <h2 class="frp-card-title">Usage Instructions</h2>
                    <p class="frp-card-text">To force a password reset for a user:</p>
                    <ol class="frp-ordered-list">
                        <li>Go to Users > All Users in the WordPress admin menu.</li>
                        <li>Click on the username to edit their profile.</li>
                        <li>Scroll down to find the "WP Force Reset Password" option.</li>
                        <li>Check the box to enable the forced password reset.</li>
                        <li>Click "Update User" to save the changes.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .frp-admin-wrap {
            margin: 20px 20px 0 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
        }

        .frp-main-title {
            color: #1e293b;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 0px solid <?php echo esc_attr($accent_color); ?>;
            position: relative;
        }

        .frp-checkbox-label input[type=checkbox] {
            display: none;
        }

        .frp-main-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, <?php echo esc_attr($accent_color); ?>, <?php echo esc_attr($accent_color); ?>80);
        }

        .frp-settings-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            max-width: 1400px;
        }

        .frp-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .frp-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .frp-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 1rem 0;
            padding: 1.5rem 1.5rem 0 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1rem;
        }

        .frp-card-subtitle {
            font-size: 1rem;
            font-weight: 600;
            color: #475569;
            margin: 1.5rem 0 0.75rem 0;
        }

        .frp-card-text {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .frp-settings-main .frp-card-title,
        .frp-settings-main .frp-card-text,
        .frp-settings-sidebar .frp-card-title,
        .frp-settings-sidebar .frp-card-text,
        .frp-settings-sidebar .frp-card-subtitle {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .frp-settings-sidebar .frp-card {
            padding-bottom: 1.5rem;
        }

        .frp-form {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        .frp-form-grid {
            display: grid;
            gap: 1.5rem;
        }

        .frp-form-group {
            display: flex;
            flex-direction: column;
        }

        .frp-form-label {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.5rem;
        }

        .frp-label-text {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .frp-label-description {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.4;
        }

        .frp-form-input,
        .frp-form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #374151;
            background: #ffffff;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .frp-form-input:focus,
        .frp-form-textarea:focus {
            outline: none;
            border-color: <?php echo esc_attr($accent_color); ?>;
            box-shadow: 0 0 0 3px <?php echo esc_attr($accent_color); ?>20;
        }

        .frp-form-textarea {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .frp-color-input-wrapper {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .frp-color-input {
            width: 50px;
            height: 40px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .frp-color-input:hover {
            border-color: <?php echo esc_attr($accent_color); ?>;
        }

        .frp-color-text {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #6b7280;
            background: #f9fafb;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
        }

        .frp-checkbox-group {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .frp-checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
            margin: 0;
        }

        .frp-checkbox-input {
            display: none;
        }

        .frp-checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: #ffffff;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .frp-checkbox-input:checked + .frp-checkbox-custom {
            background: <?php echo esc_attr($accent_color); ?>;
            border-color: <?php echo esc_attr($accent_color); ?>;
        }

        .frp-checkbox-input:checked + .frp-checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .frp-checkbox-content {
            display: flex;
            flex-direction: column;
        }

        .frp-checkbox-title {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .frp-checkbox-description {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.4;
        }

        .frp-form-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .frp-submit-btn {
            background: <?php echo esc_attr($accent_color); ?> !important;
            border-color: <?php echo esc_attr($accent_color); ?> !important;
            color: white !important;
            padding: 0.75rem 2rem !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 2px 4px <?php echo esc_attr($accent_color); ?>30 !important;
        }

        .frp-submit-btn:hover {
            background: <?php echo esc_attr($accent_color); ?>dd !important;
            border-color: <?php echo esc_attr($accent_color); ?>dd !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px <?php echo esc_attr($accent_color); ?>40 !important;
        }

        .frp-ordered-list {
            padding-left: 1.5rem;
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .frp-ordered-list li {
            margin-bottom: 0.5rem;
        }

        .frp-ordered-list li:last-child {
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .frp-settings-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .frp-admin-wrap {
                margin: 10px 10px 0 0;
            }

            .frp-main-title {
                font-size: 1.5rem;
            }

            .frp-card {
                margin-bottom: 1rem;
            }

            .frp-color-input-wrapper {
                flex-direction: column;
                align-items: stretch;
            }

            .frp-color-input {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colorInput = document.getElementById('frp_accent_color');
            const colorText = document.querySelector('.frp-color-text');
            
            colorInput.addEventListener('input', function() {
                colorText.value = this.value;
            });
        });
    </script>
    <?php
}

function force_password_reset_modal() {
    $user_id = get_current_user_id();
    $enable_2fa = get_option('frp_enable_2fa');
    $modal_heading = get_option('frp_modal_heading', 'Reset Your Password');
    $modal_description = get_option('frp_modal_description', '');
    $accent_color = get_option('frp_accent_color', '#3b82f6');

    if ($user_id && get_field('force_password_reset', 'user_' . $user_id)) {
        ?>
        <style>
            /* Dynamic accent color styles */
            :root {
                --frp-accent: <?php echo esc_attr($accent_color); ?>;
                --frp-accent-hover: <?php echo esc_attr($accent_color); ?>dd;
                --frp-accent-light: <?php echo esc_attr($accent_color); ?>20;
                --frp-accent-shadow: <?php echo esc_attr($accent_color); ?>30;
            }

            /* Modal Overlay */
            .frp-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.75);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .frp-modal-overlay.show {
                opacity: 1;
                visibility: visible;
            }
            
            /* Modal Container */
            .frp-modal {
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
                max-width: 480px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                transform: scale(0.8) translateY(20px);
                transition: all 0.3s ease;
                position: relative;
            }
            
            .frp-modal-overlay.show .frp-modal {
                transform: scale(1) translateY(0);
            }
            
            /* Modal Header */
            .frp-modal-header {
                padding: 32px 32px 24px 32px;
                border-bottom: 1px solid #e5e7eb;
                text-align: center;
            }
            
            .frp-modal-title {
                font-size: 24px;
                font-weight: 700;
                color: #111827;
                margin: 0;
                line-height: 1.3;
            }
            
            .frp-modal-description {
                color: #6b7280;
                margin: 12px 0 0 0;
                font-size: 16px;
                line-height: 1.5;
            }
            
            /* Modal Body */
            .frp-modal-body {
                padding: 32px;
            }
            
            /* Form Styles */
            .frp-form-group {
                margin-bottom: 24px;
            }
            
            .frp-form-group:last-child {
                margin-bottom: 0;
            }
            
            .frp-form-label {
                display: block;
                font-size: 14px;
                font-weight: 600;
                color: #374151;
                margin-bottom: 8px;
            }
            
            .frp-input-wrapper {
                position: relative;
            }
            
            .frp-form-input {
                width: 100%;
                height: 60px;
                padding: 16px;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                font-size: 16px;
                color: #111827;
                background: #ffffff;
                transition: all 0.2s ease;
                box-sizing: border-box;
                outline: none;
            }
            
            .frp-form-input:focus {
                border-color: var(--frp-accent);
                box-shadow: 0 0 0 3px var(--frp-accent-light);
            }
            
            .frp-form-input.error {
                border-color: #ef4444;
            }
            
            /* 6-Digit Code Input */
            .frp-code-input-container {
                display: flex;
                gap: 12px;
                justify-content: center;
                margin: 16px 0;
            }
            
            .frp-code-input {
                width: 50px;
                height: 60px;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                font-size: 24px;
                font-weight: 600;
                text-align: center;
                color: #111827;
                background: #ffffff;
                transition: all 0.2s ease;
                outline: none;
            }
            
            .frp-code-input:focus {
                border-color: var(--frp-accent);
                box-shadow: 0 0 0 3px var(--frp-accent-light);
                transform: scale(1.05);
            }
            
            .frp-code-input.filled {
                border-color: #10b981;
                background: #f0fdf4;
            }
            
            .frp-code-input.error {
                border-color: #ef4444;
                background: #fef2f2;
            }
            
            /* Password Toggle */
            .frp-password-toggle {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #6b7280;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                padding: 4px 8px;
                border-radius: 6px;
                transition: all 0.2s ease;
            }
            
            .frp-password-toggle:hover {
                color: #374151;
                background: #f3f4f6;
            }
            
            /* Error Messages */
            .frp-error-message {
                color: #ef4444;
                font-size: 14px;
                margin-top: 8px;
                font-weight: 500;
            }
            
            /* Notification */
            .frp-notification {
                padding: 16px;
                border-radius: 12px;
                margin-bottom: 24px;
                font-size: 14px;
                font-weight: 500;
                display: none;
                animation: slideDown 0.3s ease;
            }
            
            .frp-notification.success {
                background: #dcfce7;
                color: #166534;
                border: 1px solid #bbf7d0;
            }
            
            .frp-notification.error {
                background: #fef2f2;
                color: #dc2626;
                border: 1px solid #fecaca;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Buttons */
            .frp-button-group {
                display: flex;
                gap: 12px;
                margin-top: 32px;
            }
            
            .frp-button {
                flex: 1;
                padding: 16px 24px;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                border: none;
                outline: none;
                position: relative;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            
            .frp-button-primary {
                background: var(--frp-accent);
                color: white;
            }
            
            .frp-button-primary:hover {
                background: var(--frp-accent-hover);
                transform: translateY(-1px);
                box-shadow: 0 8px 25px var(--frp-accent-shadow);
            }
            
            .frp-button-secondary {
                background: #f3f4f6;
                color: #374151;
                border: 2px solid #e5e7eb;
            }
            
            .frp-button-secondary:hover {
                background: #e5e7eb;
                border-color: #d1d5db;
            }
            
            .frp-button:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none !important;
                box-shadow: none !important;
            }
            
            /* Loading State */
            .frp-button.loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid transparent;
                border-top: 2px solid currentColor;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            .frp-button.loading {
                color: transparent;
            }
            
            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
            
            /* Step Transitions */
            .frp-step {
                transition: all 0.3s ease;
            }
            
            .frp-step.hidden {
                display: none;
            }
            
            /* Mobile Responsive */
            @media (max-width: 640px) {
                .frp-modal {
                    margin: 20px;
                    width: calc(100% - 40px);
                }
                
                .frp-modal-header {
                    padding: 24px 24px 20px 24px;
                }
                
                .frp-modal-body {
                    padding: 24px;
                }
                
                .frp-modal-title {
                    font-size: 20px;
                }
                
                .frp-button-group {
                    flex-direction: column;
                }
                
                .frp-form-input {
                    padding: 14px;
                }
                
                .frp-button {
                    padding: 14px 20px;
                }
            }
        </style>

        <!-- Custom Modal -->
        <div class="frp-modal-overlay" id="frpModalOverlay">
            <div class="frp-modal" id="frpModal">
                <div class="frp-modal-header">
                    <h2 class="frp-modal-title"><?php echo esc_html($modal_heading); ?></h2>
                    <?php if (!empty($modal_description)): ?>
                        <p class="frp-modal-description"><?php echo esc_html($modal_description); ?></p>
                    <?php endif; ?>
                </div>
                <div class="frp-modal-body">
                    <div id="frpNotification" class="frp-notification"></div>

                    <?php if ($enable_2fa): ?>
                    <div id="frp2faStep" class="frp-step">
                        <form id="frp2faForm">
                            <div class="frp-form-group">
                                <label for="frp2faCode" class="frp-form-label">Enter 6-digit code</label>
                                <div class="frp-code-input-container">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="0">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="1">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="2">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="3">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="4">
                                    <input type="text" class="frp-code-input" maxlength="1" data-index="5">
                                </div>
                                <div id="frp2faCodeError" class="frp-error-message"></div>
                            </div>
                            <div class="frp-button-group">
                                <button type="button" class="frp-button frp-button-secondary" id="frpSend2faCode">Send New Code</button>
                                <button type="submit" class="frp-button frp-button-primary">Verify Code</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div id="frpPasswordStep" class="frp-step <?php echo $enable_2fa ? 'hidden' : ''; ?>">
                        <form id="frpPasswordForm">
                            <div class="frp-form-group">
                                <label for="frpNewPassword" class="frp-form-label">New Password</label>
                                <div class="frp-input-wrapper">
                                    <input type="password" class="frp-form-input" id="frpNewPassword" name="new_password" required>
                                    <button type="button" class="frp-password-toggle" data-target="frpNewPassword">Show</button>
                                </div>
                                <div id="frpNewPasswordError" class="frp-error-message"></div>
                            </div>
                            <div class="frp-form-group">
                                <label for="frpConfirmPassword" class="frp-form-label">Confirm Password</label>
                                <div class="frp-input-wrapper">
                                    <input type="password" class="frp-form-input" id="frpConfirmPassword" name="confirm_password" required>
                                    <button type="button" class="frp-password-toggle" data-target="frpConfirmPassword">Show</button>
                                </div>
                                <div id="frpConfirmPasswordError" class="frp-error-message"></div>
                            </div>
                            <div class="frp-button-group">
                                <button type="submit" class="frp-button frp-button-primary">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const modalOverlay = document.getElementById('frpModalOverlay');
                const modal = document.getElementById('frpModal');
                const notification = document.getElementById('frpNotification');
                
                // Prevent body scroll when modal is open
                document.body.style.overflow = 'hidden';
                
                // Show modal with animation
                setTimeout(() => {
                    modalOverlay.classList.add('show');
                }, 100);

                function showNotification(message, isSuccess) {
                    notification.className = 'frp-notification ' + (isSuccess ? 'success' : 'error');
                    notification.textContent = message;
                    notification.style.display = 'block';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 5000);
                }

                function setButtonLoading(button, loading) {
                    if (loading) {
                        button.classList.add('loading');
                        button.disabled = true;
                    } else {
                        button.classList.remove('loading');
                        button.disabled = false;
                    }
                }

                // Password visibility toggle
                document.querySelectorAll('.frp-password-toggle').forEach(toggle => {
                    toggle.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-target');
                        const passwordInput = document.getElementById(targetId);
                        const type = passwordInput.type === 'password' ? 'text' : 'password';
                        passwordInput.type = type;
                        this.textContent = type === 'password' ? 'Show' : 'Hide';
                    });
                });

                // 6-digit code input handling
                const codeInputs = document.querySelectorAll('.frp-code-input');
                
                function getCodeValue() {
                    return Array.from(codeInputs).map(input => input.value).join('');
                }
                
                function clearCodeInputs() {
                    codeInputs.forEach((input, index) => {
                        input.value = '';
                        input.classList.remove('filled', 'error');
                        if (index === 0) input.focus();
                    });
                }
                
                codeInputs.forEach((input, index) => {
                    input.addEventListener('input', function(e) {
                        const value = e.target.value;
                        
                        // Only allow numbers
                        if (!/^\d*$/.test(value)) {
                            e.target.value = '';
                            return;
                        }
                        
                        if (value) {
                            e.target.classList.add('filled');
                            e.target.classList.remove('error');
                            
                            // Move to next input
                            if (index < codeInputs.length - 1) {
                                codeInputs[index + 1].focus();
                            }
                        } else {
                            e.target.classList.remove('filled');
                        }
                    });
                    
                    input.addEventListener('keydown', function(e) {
                        // Handle backspace
                        if (e.key === 'Backspace' && !e.target.value && index > 0) {
                            codeInputs[index - 1].focus();
                            codeInputs[index - 1].value = '';
                            codeInputs[index - 1].classList.remove('filled');
                        }
                        
                        // Handle paste
                        if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                            e.preventDefault();
                            navigator.clipboard.readText().then(text => {
                                const numbers = text.replace(/\D/g, '').slice(0, 6);
                                codeInputs.forEach((input, i) => {
                                    input.value = numbers[i] || '';
                                    if (numbers[i]) {
                                        input.classList.add('filled');
                                    } else {
                                        input.classList.remove('filled');
                                    }
                                });
                                if (numbers.length > 0) {
                                    const lastFilledIndex = Math.min(numbers.length - 1, 5);
                                    codeInputs[lastFilledIndex].focus();
                                }
                            });
                        }
                        
                        // Handle arrow keys
                        if (e.key === 'ArrowLeft' && index > 0) {
                            codeInputs[index - 1].focus();
                        }
                        if (e.key === 'ArrowRight' && index < codeInputs.length - 1) {
                            codeInputs[index + 1].focus();
                        }
                    });
                });
                
                // Focus first input initially
                if (codeInputs.length > 0) {
                    codeInputs[0].focus();
                }

                <?php if ($enable_2fa): ?>
                function send2FACode() {
                    const sendBtn = document.getElementById('frpSend2faCode');
                    setButtonLoading(sendBtn, true);
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=send_2fa_code'
                    })
                    .then(response => response.text())
                    .then(data => {
                        setButtonLoading(sendBtn, false);
                        if (data === 'success') {
                            showNotification('A 6-digit code has been sent to your email. Check spam folder if you don\'t see it.', true);
                        } else {
                            showNotification('Failed to send email. Please try again.', false);
                        }
                    })
                    .catch(() => {
                        setButtonLoading(sendBtn, false);
                        showNotification('An error occurred. Please try again.', false);
                    });
                }

                // Auto-send 2FA code when modal shows
                send2FACode();

                document.getElementById('frpSend2faCode').addEventListener('click', send2FACode);

                document.getElementById('frp2faForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const code = getCodeValue();
                    const submitBtn = this.querySelector('button[type="submit"]');
                    
                    if (code.length !== 6) {
                        codeInputs.forEach(input => input.classList.add('error'));
                        document.getElementById('frp2faCodeError').textContent = 'Please enter all 6 digits';
                        return;
                    }
                    
                    // Clear any previous errors
                    codeInputs.forEach(input => input.classList.remove('error'));
                    document.getElementById('frp2faCodeError').textContent = '';
                    
                    setButtonLoading(submitBtn, true);
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=verify_2fa_code&code=' + encodeURIComponent(code)
                    })
                    .then(response => response.text())
                    .then(data => {
                        setButtonLoading(submitBtn, false);
                        if (data === 'success') {
                            document.getElementById('frp2faStep').classList.add('hidden');
                            document.getElementById('frpPasswordStep').classList.remove('hidden');
                        } else {
                            codeInputs.forEach(input => input.classList.add('error'));
                            showNotification('Invalid code. Please try again.', false);
                            setTimeout(() => {
                                clearCodeInputs();
                            }, 1000);
                        }
                    })
                    .catch(() => {
                        setButtonLoading(submitBtn, false);
                        showNotification('An error occurred. Please try again.', false);
                    });
                });
                <?php endif; ?>

                document.getElementById('frpPasswordForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const newPassword = document.getElementById('frpNewPassword').value;
                    const confirmPassword = document.getElementById('frpConfirmPassword').value;
                    const submitBtn = this.querySelector('button[type="submit"]');
                    let isValid = true;

                    // Clear previous errors
                    document.getElementById('frpNewPasswordError').textContent = '';
                    document.getElementById('frpConfirmPasswordError').textContent = '';
                    document.getElementById('frpNewPassword').classList.remove('error');
                    document.getElementById('frpConfirmPassword').classList.remove('error');

                    const passwordPattern = /^(?=.*[A-Z])(?=.*\W).{8,}$/;

                    if (!passwordPattern.test(newPassword)) {
                        document.getElementById('frpNewPasswordError').textContent = 'Password must be at least 8 characters long, include at least one capital letter and one special character.';
                        document.getElementById('frpNewPassword').classList.add('error');
                        isValid = false;
                    }

                    if (newPassword !== confirmPassword) {
                        document.getElementById('frpConfirmPasswordError').textContent = 'Passwords do not match.';
                        document.getElementById('frpConfirmPassword').classList.add('error');
                        isValid = false;
                    }

                    if (isValid) {
                        setButtonLoading(submitBtn, true);
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=force_password_reset_action&new_password=' + encodeURIComponent(newPassword) + '&confirm_password=' + encodeURIComponent(confirmPassword)
                        })
                        .then(response => response.text())
                        .then(data => {
                            setButtonLoading(submitBtn, false);
                            if (data === 'success') {
                                showNotification('Password reset successful. Redirecting...', true);
                                setTimeout(() => {
                                    window.location.href = '<?php echo home_url(); ?>';
                                }, 2000);
                            } else {
                                showNotification('Failed to reset password. Please try again.', false);
                            }
                        })
                        .catch(() => {
                            setButtonLoading(submitBtn, false);
                            showNotification('An error occurred. Please try again.', false);
                        });
                    }
                });
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'force_password_reset_modal');

function handle_force_password_reset() {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'send_2fa_code':
                send_2fa_code();
                break;
            case 'verify_2fa_code':
                verify_2fa_code();
                break;
            case 'force_password_reset_action':
                process_password_reset();
                break;
        }
    }
}
add_action('init', 'handle_force_password_reset');

function send_2fa_code() {
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $code = sprintf('%06d', mt_rand(0, 999999));
    update_user_meta($user_id, '2fa_code', $code);
    
    $to = $user->user_email;
    $subject = 'Your 2FA Code for Password Reset';
    $message = "Your 6-digit code is: $code";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $sent = wp_mail($to, $subject, $message, $headers);
    wp_die($sent ? 'success' : 'error');
}

function verify_2fa_code() {
    $user_id = get_current_user_id();
    $stored_code = get_user_meta($user_id, '2fa_code', true);
    $submitted_code = sanitize_text_field($_POST['code']);
    
    if ($stored_code === $submitted_code) {
        delete_user_meta($user_id, '2fa_code');
        wp_die('success');
    } else {
        wp_die('error');
    }
}

function process_password_reset() {
    $user_id = get_current_user_id();
    if ($user_id) {
        $new_password = sanitize_text_field($_POST['new_password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);

        if ($new_password === $confirm_password && !empty($new_password)) {
            wp_set_password($new_password, $user_id);
            update_field('force_password_reset', false, 'user_' . $user_id);
            
            // Log the user back in
            $user = get_user_by('ID', $user_id);
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user->user_login, $user);
            
            wp_die('success');
        }
    }
    wp_die('error');
}

// Add these actions to handle AJAX requests
add_action('wp_ajax_send_2fa_code', 'send_2fa_code');
add_action('wp_ajax_verify_2fa_code', 'verify_2fa_code');
add_action('wp_ajax_force_password_reset_action', 'process_password_reset');

// Add this action to define ajaxurl for front-end
add_action('wp_head', function() {
    echo '<script type="text/javascript">
        var ajaxurl = "' . admin_url('admin-ajax.php') . '";
    </script>';
});

// Set the 'force_password_reset' field to true by default for new users
function set_default_force_password_reset($user_id) {
    if (function_exists('update_field')) {
        update_field('force_password_reset', true, 'user_' . $user_id);
    }
}
add_action('user_register', 'set_default_force_password_reset');
