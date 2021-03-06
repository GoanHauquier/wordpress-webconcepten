<?php
namespace Essential_Addons_Elementor\Traits;

if (!defined('ABSPATH')) {
    exit();
}
// Exit if accessed directly

use Essential_Addons_Elementor\Classes\WPDeveloper_Notice;

trait Admin
{
    /**
     * Create an admin menu.
     *
     * @since 1.1.2
     */
    public function admin_menu()
    {
        add_menu_page(
            __('Essential Addons', 'Essential Addons'),
            __('Essential Addons', 'essential-addons-elementor'),
            'manage_options',
            'eael-settings',
            [$this, 'eael_admin_settings_page'],
            $this->safe_protocol(EAEL_PLUGIN_URL . '/assets/admin/images/ea-icon-white.svg'),
            '58.6'
        );
    }

    /**
     * Loading all essential scripts
     *
     * @since 1.1.2
     */
    public function admin_enqueue_scripts($hook)
    {
        wp_enqueue_style('essential_addons_elementor-notice-css', EAEL_PLUGIN_URL . '/assets/admin/css/eael-notice.css', false, EAEL_PLUGIN_VERSION);

        if (isset($hook) && $hook == 'toplevel_page_eael-settings') {
            wp_enqueue_style('essential_addons_elementor-admin-css', EAEL_PLUGIN_URL . '/assets/admin/css/admin.css', false, EAEL_PLUGIN_VERSION);
            if ($this->pro_enabled) {
                wp_enqueue_style('eael_pro-admin-css', EAEL_PRO_PLUGIN_URL . '/assets/admin/css/admin.css', false, EAEL_PRO_PLUGIN_VERSION);
            }
            wp_enqueue_style('sweetalert2-css', EAEL_PLUGIN_URL . '/assets/admin/vendor/sweetalert2/css/sweetalert2.min.css', false, EAEL_PLUGIN_VERSION);
            wp_enqueue_script('sweetalert2-js', EAEL_PLUGIN_URL . '/assets/admin/vendor/sweetalert2/js/sweetalert2.min.js', array('jquery', 'sweetalert2-core-js'), EAEL_PLUGIN_VERSION, true);
            wp_enqueue_script('sweetalert2-core-js', EAEL_PLUGIN_URL . '/assets/admin/vendor/sweetalert2/js/core.js', array('jquery'), EAEL_PLUGIN_VERSION, true);
            wp_enqueue_script('essential_addons_elementor-admin-js', EAEL_PLUGIN_URL . '/assets/admin/js/admin.js', array('jquery'), EAEL_PLUGIN_VERSION, true);

            wp_localize_script('essential_addons_elementor-admin-js', 'localize', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('essential-addons-elementor'),
            ));
        }
    }

    /**
     * Create settings page.
     *
     * @since 1.1.2
     */
    public function eael_admin_settings_page()
    {
        ?>
        <div class="eael-settings-wrap">
		  	<form action="" method="POST" id="eael-settings" name="eael-settings">
		  		<div class="eael-header-bar">
					<div class="eael-header-left">
						<div class="eael-admin-logo-inline">
							<img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-ea-logo.svg'; ?>">
						</div>
						<h2 class="title"><?php echo __('Essential Addons Settings', 'essential-addons-elementor'); ?></h2>
					</div>
					<div class="eael-header-right">
					<button type="submit" class="button eael-btn js-eael-settings-save"><?php echo __('Save settings', 'essential-addons-elementor'); ?></button>
					</div>
				</div>
			  	<div class="eael-settings-tabs">
			    	<ul class="eael-tabs">
				      	<li><a href="#general" class="active"><img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-general.svg'; ?>"><span><?php echo __('General', 'essential-addons-elementor'); ?></span></a></li>
				      	<li><a href="#elements"><img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-elements.svg'; ?>"><span><?php echo __('Elements', 'essential-addons-elementor'); ?></span></a></li>
                        <li><a href="#extensions"><img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-extensions.svg'; ?>"><span><?php echo __('Extensions', 'essential-addons-elementor'); ?></span></a></li>
                        <li><a href="#tools"><img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-tools.svg'; ?>"><span><?php echo __('Tools', 'essential-addons-elementor'); ?></span></a></li>
                        <?php if (!$this->pro_enabled) {?>
                            <li><a href="#go-pro"><img src="<?php echo EAEL_PLUGIN_URL . '/assets/admin/images/icon-upgrade.svg'; ?>"><span><?php echo __('Go Premium', 'essential-addons-elementor'); ?></span></a></li>
                        <?php }?>
                    </ul>
                    <?php
                        include_once EAEL_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes/templates/admin/general.php';
                        include_once EAEL_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes/templates/admin/elements.php';
                        include_once EAEL_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes/templates/admin/extensions.php';
                        include_once EAEL_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes/templates/admin/tools.php';
                        if (!$this->pro_enabled) {
                            include_once EAEL_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes/templates/admin/go-pro.php';
                        }
                    ?>
                </div>
            </form>
        </div>
        <?php
}

    /**
     * Saving data with ajax request
     * @param
     * @return  array
     * @since 1.1.2
     */
    public function save_settings()
    {
        check_ajax_referer('essential-addons-elementor', 'security');

        if (!isset($_POST['fields'])) {
            return;
        }

        parse_str($_POST['fields'], $settings);

        // update new settings
        $updated = update_option('eael_save_settings', array_merge(array_fill_keys($this->get_registered_elements(), 0), array_map(function ($value) {return 1;}, $settings)));

        // Saving Google Map Api Key
        update_option('eael_save_google_map_api', @$settings['google-map-api']);

        // Saving Mailchimp Api Key
        update_option('eael_save_mailchimp_api', @$settings['mailchimp-api']);

        // Build assets files
        $this->generate_scripts(array_keys($settings));

        wp_send_json($updated);
    }

    public function admin_notice()
    {
        $notice = new WPDeveloper_Notice(EAEL_PLUGIN_BASENAME, EAEL_PLUGIN_VERSION);
        $notice->finish_time['update'] = 'May 28, 2019';
        $scheme = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
        $url = $_SERVER['REQUEST_URI'] . $scheme;
        $notice->links = [
            'review' => array(
                'later' => array(
                    'link' => 'https://wpdeveloper.net/review-essential-addons-elementor',
                    'target' => '_blank',
                    'label' => __('Ok, you deserve it!', 'essential-addons-elementor'),
                    'icon_class' => 'dashicons dashicons-external',
                ),
                'allready' => array(
                    'link' => $url,
                    'label' => __('I already did', 'essential-addons-elementor'),
                    'icon_class' => 'dashicons dashicons-smiley',
                    'data_args' => [
                        'dismiss' => true,
                    ],
                ),
                'maybe_later' => array(
                    'link' => $url,
                    'label' => __('Maybe Later', 'essential-addons-elementor'),
                    'icon_class' => 'dashicons dashicons-calendar-alt',
                    'data_args' => [
                        'later' => true,
                    ],
                ),
                'support' => array(
                    'link' => 'https://wpdeveloper.net/support',
                    'label' => __('I need help', 'essential-addons-elementor'),
                    'icon_class' => 'dashicons dashicons-sos',
                ),
                'never_show_again' => array(
                    'link' => $url,
                    'label' => __('Never show again', 'essential-addons-elementor'),
                    'icon_class' => 'dashicons dashicons-dismiss',
                    'data_args' => [
                        'dismiss' => true,
                    ],
                ),
            ),
        ];

        /**
         * This is review message and thumbnail.
         */
        $notice->message('review', '<p>' . __('We hope you\'re enjoying Essential Addons for Elementor! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'essential-addons-elementor') . '</p>');
        $notice->thumbnail('review', plugins_url('assets/admin/images/ea-logo.svg', EAEL_PLUGIN_BASENAME));

         /**
         * This is update message and thumbnail.
         */
        $notice->message('update', '<p>' . __("Get 20% Discount & Turbo-Charge Your <strong>Elementor</strong> Page Building With <strong>Essential Addons PRO</strong>. Use Coupon Code <span class='coupon-code'>SpeedUp</span> on checkout. <a class='ea-notice-cta' target='_blank' href='https://wpdeveloper.net/plugins/essential-addons-elementor#pricing'>Redeem Now</a>", 'essential-addons-elementor') . '<button class="notice-dismiss" data-notice="update"></button></p>');
        $notice->thumbnail('update', plugins_url('assets/admin/images/icon-bolt.svg', EAEL_PLUGIN_BASENAME));

        /**
         * Current Notice End Time.
         * Notice will dismiss in 3 days if user does nothing.
         */
        $notice->cne_time = '3 Day';
        /**
         * Current Notice Maybe Later Time.
         * Notice will show again in 7 days
         */
        $notice->maybe_later_time = '7 Day';

        $notice->text_domain = 'essential-addons-elementor';

        $notice->options_args = array(
            'notice_will_show' => [
                'update' => $notice->timestamp,
                'opt_in' => $notice->makeTime($notice->timestamp, '1 Day'),
                'review' => $notice->makeTime($notice->timestamp, '4 Day'), // after 4 days
            ],
        );

        $notice->init();
    }
}
