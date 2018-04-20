<?php

namespace WPGDPRC\Includes;

/**
 * Class Pages
 * @package WPGDPRC\Includes
 */
class Pages {
    /** @var null */
    private static $instance = null;

    public function registerSettings() {
        foreach (Helpers::getCheckList() as $id => $check) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_general_' . $id, 'intval');
        }
        register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_settings_privacy_policy_page', 'intval');
        register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_settings_privacy_policy_text', array('sanitize_callback' => array(Helpers::getInstance(), 'sanitizeData')));
        register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_settings_enable_request_user_data', 'intval');
    }

    public function addAdminMenu() {
        $pluginData = Helpers::getPluginData();
        add_submenu_page(
            'tools.php',
            $pluginData['Name'],
            $pluginData['Name'],
            'manage_options',
            str_replace('-', '_', WP_GDPR_C_SLUG),
            array($this, 'generatePage')
        );
    }

    public function generatePage() {
        $pluginData = Helpers::getPluginData();
        $daysLeftToComply = Helpers::getDaysLeftToComply();
        $enableRequestUserData = Helpers::isEnabled('enable_request_user_data', 'settings');
        ?>
        <div class="wrap">
            <div class="wpgdprc">
                <h1 class="wpgdprc-title"><span class="dashicons dashicons-lock"></span> <?php echo $pluginData['Name']; ?></h1>

                <?php settings_errors(); ?>

                <form method="post" action="<?php echo admin_url('options.php'); ?>" novalidate="novalidate">
                    <?php settings_fields(WP_GDPR_C_SLUG); ?>

                    <div class="wpgdprc-tabs">
                        <div class="wpgdprc-tabs__navigation wpgdprc-clearfix">
                            <a id="tab-integrations-label" class="active" href="#tab-integrations" aria-controls="tab-integrations" tabindex="0" role="tab"><?php _e('Integrations', WP_GDPR_C_SLUG); ?></a>
                            <?php if ($enableRequestUserData) : ?>
                                <a id="tab-requests-label" href="#tab-requests" aria-controls="tab-requests" tabindex="-1" role="tab"><?php _e('Requests', WP_GDPR_C_SLUG); ?></a>
                            <?php endif; ?>
                            <a id="tab-checklist-label" href="#tab-checklist" aria-controls="tab-checklist" tabindex="-1" role="tab"><?php _e('Checklist', WP_GDPR_C_SLUG); ?></a>
                            <a id="tab-settings-label" href="#tab-settings" aria-controls="tab-settings" tabindex="-1" role="tab"><?php _e('Settings', WP_GDPR_C_SLUG); ?></a>
                        </div>

                        <div class="wpgdprc-tabs__content">
                            <div id="tab-integrations" class="wpgdprc-tabs__panel wpgdprc-clearfix active" aria-labelledby="tab-integrations-label" role="tabpanel">
                                <?php self::getIntegrationsTab(); ?>
                            </div>
                            <?php if ($enableRequestUserData) : ?>
                                <div id="tab-requests" class="wpgdprc-tabs__panel wpgdprc-clearfix" aria-hidden="true" aria-labelledby="tab-requests-label" role="tabpanel">
                                    <?php self::getRequestsTab(); ?>
                                </div>
                            <?php endif; ?>
                            <div id="tab-checklist" class="wpgdprc-tabs__panel wpgdprc-clearfix" aria-hidden="true" aria-labelledby="tab-checklist-label" role="tabpanel">
                                <?php self::getChecklistTab(); ?>
                            </div>
                            <div id="tab-settings" class="wpgdprc-tabs__panel wpgdprc-clearfix" aria-hidden="true" aria-labelledby="tab-settings-label" role="tabpanel">
                                <?php self::getSettingsTab(); ?>
                            </div>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>

                <div class="wpgdprc-description">
                    <p><?php printf(__('This plugin assists website and webshop owners to comply with European privacy regulations known as GDPR. By May 24th, 2018 your site or shop has to comply to avoid large fines. The regulation can be read here: %s.', WP_GDPR_C_SLUG), '<a target="_blank" href="//www.eugdpr.org/the-regulation.html">' . __('GDPR Key Changes', WP_GDPR_C_SLUG) . '</a>'); ?></p>
                    <p><?php printf(__('%s currently supports %s.', WP_GDPR_C_SLUG), $pluginData['Name'], implode(', ', Integrations::getSupportedIntegrationsLabels())); ?></p>
                </div>

                <p class="wpgdprc-disclaimer"><?php _e('Disclaimer: The creators of this plugin do not have a legal background please contact a law firm for rock solid legal advice.', WP_GDPR_C_SLUG); ?></p>

                <?php if ($daysLeftToComply > 0) : ?>
                    <div class="wpgdprc-countdown">
                        <div class="wpgdprc-countdown-inner">
                            <h2><?php echo date(get_option('date_format'), strtotime('25 May 2018')); ?></h2>
                            <p><?php printf(__('You have %s left to comply with GDPR.', WP_GDPR_C_SLUG), sprintf(_n('%s day', '%s days', $daysLeftToComply, WP_GDPR_C_SLUG), number_format_i18n($daysLeftToComply))); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wpgdprc-background"><?php include(WP_GDPR_C_DIR_SVG . '/inline-waves.svg.php'); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Tab: Integrations
     */
    private static function getIntegrationsTab() {
        $pluginData = Helpers::getPluginData();
        $activatedPlugins = Helpers::getActivatedPlugins();
        ?>
        <?php if (!empty($activatedPlugins)) : ?>
            <ul class="wpgdprc-list">
                <?php
                foreach ($activatedPlugins as $key => $plugin) :
                    $optionName = WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'];
                    $checked = Helpers::isEnabled($plugin['id']);
                    $description = (!empty($plugin['description'])) ? apply_filters('the_content', $plugin['description']) : '';
                    $notices = Helpers::getNotices($plugin['id']);
                    $options = Integrations::getSupportedPluginOptions($plugin['id']);
                    ?>
                    <li class="wpgdprc-clearfix">
                        <?php if ($plugin['supported']) : ?>
                            <?php if (empty($notices)) : ?>
                                <div class="wpgdprc-checkbox">
                                    <input type="checkbox" name="<?php echo $optionName; ?>" id="<?php echo $optionName; ?>" value="1" tabindex="1" data-type="save_setting" data-option="<?php echo $optionName; ?>" <?php checked(true, $checked); ?> />
                                    <label for="<?php echo $optionName; ?>"><?php echo $plugin['name']; ?></label>
                                    <span class="wpgdprc-instructions"><?php _e('Enable:', WP_GDPR_C_SLUG); ?></span>
                                    <div class="wpgdprc-switch" aria-hidden="true">
                                        <div class="wpgdprc-switch-label">
                                            <div class="wpgdprc-switch-inner"></div>
                                            <div class="wpgdprc-switch-switch"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="wpgdprc-checkbox-data" <?php if (!$checked) : ?>style="display: none;"<?php endif; ?>>
                                    <?php if (!empty($description)) : ?>
                                        <div class="wpgdprc-checklist-description">
                                            <?php echo $description; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo $options; ?>
                                </div>
                            <?php else : ?>
                                <div class="wpgdrc-message wpgdrc-message--notice">
                                    <strong><?php echo $plugin['name']; ?></strong>
                                    <div class="wpgdprc__message">
                                        <?php echo $notices; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="wpgdrc-message wpgdrc-message--error">
                                <strong><?php echo $plugin['name']; ?></strong>
                                <div class="wpgdprc__message">
                                    <?php printf(__('This plugin is outdated. %s supports version %s and up.', WP_GDPR_C_SLUG), $pluginData['Name'], '<strong>' . $plugin['supported_version']  . '</strong>'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php
                endforeach;
                ?>
            </ul>
        <?php else : ?>
            <p><strong><?php _e('Couldn\'t find any supported plugins installed.', WP_GDPR_C_SLUG); ?></strong></p>
            <p><?php _e('The following plugins are supported as of now:', WP_GDPR_C_SLUG); ?></p>
            <ul class="ul-square">
                <?php foreach (Integrations::getSupportedPlugins() as $plugin) : ?>
                    <li><?php echo $plugin['name']; ?></li>
                <?php endforeach; ?>
            </ul>
            <p><?php _e('More plugins will be added in the future.', WP_GDPR_C_SLUG); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Tab: Checklist
     */
    private static function getChecklistTab() {
        ?>
        <p><?php _e('Below we ask you what private data you currently collect and provide you with tips to comply.', WP_GDPR_C_SLUG); ?></p>
        <ul class="wpgdprc-list">
            <?php
            foreach (Helpers::getCheckList() as $id => $check) :
                $optionName = WP_GDPR_C_PREFIX . '_general_' . $id;
                $checked = Helpers::isEnabled($id, 'general');
                $description = (!empty($check['description'])) ? esc_html($check['description']) : '';
                ?>
                <li class="wpgdprc-clearfix">
                    <div class="wpgdprc-checkbox">
                        <input type="checkbox" name="<?php echo $optionName; ?>" id="<?php echo $id; ?>" value="1" tabindex="1" data-type="save_setting" data-option="<?php echo $optionName; ?>" <?php checked(true, $checked); ?> />
                        <label for="<?php echo $id; ?>"><?php echo $check['label']; ?></label>
                        <div class="wpgdprc-switch wpgdprc-switch--reverse" aria-hidden="true">
                            <div class="wpgdprc-switch-label">
                                <div class="wpgdprc-switch-inner"></div>
                                <div class="wpgdprc-switch-switch"></div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($description)) : ?>
                        <div class="wpgdprc-checkbox-data" <?php if (!$checked) : ?>style="display: none;"<?php endif; ?>>
                            <div class="wpgdprc-checklist-description">
                                <?php echo $description; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php
            endforeach;
            ?>
        </ul>
        <?php
    }

    /**
     * Tab: Settings
     */
    private static function getSettingsTab() {
        $optionNamePrivacyPolicyPage = WP_GDPR_C_PREFIX . '_settings_privacy_policy_page';
        $optionNamePrivacyPolicyText = WP_GDPR_C_PREFIX . '_settings_privacy_policy_text';
        $optionNameEnableRequestUserData = WP_GDPR_C_PREFIX . '_settings_enable_request_user_data';
        $privacyPolicyPage = get_option($optionNamePrivacyPolicyPage);
        $privacyPolicyText = esc_html(Integrations::getPrivacyPolicyText());
        $enableRequestUserData = Helpers::isEnabled('enable_request_user_data', 'settings');
        ?>
        <p><?php _e('Use %privacy_policy% if you want to link your Privacy Policy page in the GDPR checkbox texts.', WP_GDPR_C_SLUG); ?></p>
        <p><strong><?php _e('Privacy Policy', WP_GDPR_C_SLUG); ?></strong></p>
        <p class="wpgdprc-setting">
            <label for="<?php echo $optionNamePrivacyPolicyPage; ?>"><?php _e('Privacy Policy', WP_GDPR_C_SLUG); ?></label>
            <?php
            wp_dropdown_pages(array(
                'show_option_none' => __('Select an option', WP_GDPR_C_SLUG),
                'name' => $optionNamePrivacyPolicyPage,
                'selected' => $privacyPolicyPage
            ));
            ?>
        </p>
        <p class="wpgdprc-setting">
            <label for="<?php echo $optionNamePrivacyPolicyText; ?>"><?php _e('Link text', WP_GDPR_C_SLUG); ?></label>
            <input type="text" name="<?php echo $optionNamePrivacyPolicyText; ?>" class="regular-text" id="<?php echo $optionNamePrivacyPolicyText; ?>" placeholder="<?php echo $privacyPolicyText; ?>" value="<?php echo $privacyPolicyText; ?>" />
        </p>
        <p><strong><?php _e('Request User Data', WP_GDPR_C_SLUG); ?></strong></p>
        <p class="wpgdprc-setting">
            <label for="<?php echo $optionNameEnableRequestUserData; ?>"><?php _e('Enable', WP_GDPR_C_SLUG); ?></label>
            <label><input type="checkbox" name="<?php echo $optionNameEnableRequestUserData; ?>" id="<?php echo $optionNameEnableRequestUserData; ?>" value="1" tabindex="1" data-type="save_setting" data-option="<?php echo $optionNameEnableRequestUserData; ?>" <?php checked(true, $enableRequestUserData); ?> /> <?php _e('Yes', WP_GDPR_C_SLUG); ?></label>
        </p>
        <div class="wpgdprc-information">
            <?php
            printf(
                '<strong>%s:</strong> %s',
                strtoupper(__('Note', WP_GDPR_C_SLUG)),
                __('Enabling this will create a page with...', WP_GDPR_C_SLUG)
            );
            ?>
        </div>
        <?php
    }

    /**
     * Tab: Requests
     */
    private static function getRequestsTab() {
        $requests = Request::getInstance()->getList();
        if (!empty($requests)) :
            ?>
            <table class="wpgdprc-table">
                <thead>
                <tr>
                    <th scope="col" width="10%"><?php _e('Request', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="42%"><?php _e('Email Address', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="18%"><?php _e('IP Address', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="22%"><?php _e('Date', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="8%"><?php _e('Active', WP_GDPR_C_SLUG); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var Request $request */
                foreach ($requests as $request) :
                    ?>
                    <tr>
                        <td><?php printf('#%d', $request->getId()); ?></td>
                        <td><?php echo $request->getEmailAddress(); ?></td>
                        <td><?php echo $request->getIpAddress(); ?></td>
                        <td><?php echo $request->getDateCreated(); ?></td>
                        <td><span class="dashicons dashicons-<?php echo ($request->getActive()) ? 'yes' : 'no'; ?>"></span></td>
                    </tr>
                    <?php
                endforeach;
                ?>
                </tbody>
            </table>
            <?php
        else :
            ?>
            <p><strong><?php _e('No requests found.', WP_GDPR_C_SLUG); ?></strong></p>
            <?php
        endif;
    }

    /**
     * @return null|Pages
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}