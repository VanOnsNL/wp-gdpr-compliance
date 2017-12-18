<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;

/**
 * Class Pages
 * @package WPGDPRC\Includes
 */
class Pages {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|Pages
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerSettings() {
        foreach (Helpers::getCheckList() as $id => $check) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_general_' . $id, 'intval');
        }
    }

    public function addAdminMenu() {
        $pluginData = Helpers::getPluginData();
        add_submenu_page(
            'tools.php',
            $pluginData['Name'],
            $pluginData['Name'],
            'manage_options',
            'wp_gdpr_compliance',
            array($this, 'generatePage')
        );
    }

    public function generatePage() {
        $pluginData = Helpers::getPluginData();
        $activatedPlugins = Helpers::getActivatedPlugins();
        ?>
        <div class="wrap">
            <div class="wpgdprc">
                <h1 class="wpgdprc-title"><i class="fa fa-lock" aria-hidden="true"></i> <?php echo $pluginData['Name']; ?></h1>

                <p class="wpgdprc-description">
                    <?php _e('This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR).
                    By May 24th, 2018 your website or shop has to comply to avoid large fines. The regulation can be read here:', WP_GDPR_C_SLUG) ?>
                    <a target="_blank" href="//<?php _e('www.eugdpr.org/the-regulation.html', WP_GDPR_C_SLUG) ?>"><?php _e('GDPR Key Changes', WP_GDPR_C_SLUG) ?></a></p>

                <form method="post" action="<?php echo admin_url('options.php'); ?>" novalidate="novalidate">
                    <?php settings_fields(WP_GDPR_C_SLUG); ?>

                    <div class="wpgdprc-tabs">
                        <div class="wpgdprc-tabs__navigation cf">
                            <a id="tab-general-label" class="active" href="#tab-general" aria-selected="true" aria-controls="tab-general" tabindex="0" role="tab"><?php _e('General', WP_GDPR_C_SLUG); ?></a>
                            <a id="tab-integrations-label" href="#tab-integrations" aria-controls="tab-integrations" tabindex="-1" role="tab"><?php _e('Integrations', WP_GDPR_C_SLUG); ?></a>
                        </div>

                        <div class="wpgdprc-tabs__content">
                            <div id="tab-general" class="wpgdprc-tabs__panel active" aria-labelledby="tab-general-label" role="tabpanel">
                                <p><?php _e('Below we ask you what private data you currently collect and provide you with tips to comply.', WP_GDPR_C_SLUG) ?></p>

                                <ul class="wpgdprc-list">
                                    <?php
                                    foreach (Helpers::getCheckList() as $id => $check) :
                                        $optionName = WP_GDPR_C_PREFIX . '_general_' . $id;
                                        $checked = Helpers::isEnabled($id, 'general');
                                        $description = (!empty($check['description'])) ? esc_html($check['description']) : '';
                                        ?>
                                        <li>
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
                                                <div class="wpgdprc-checklist-description" <?php if (!$checked) : ?>style="display: none;"<?php endif; ?>>
                                                    <?php echo $description; ?>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div id="tab-integrations" class="wpgdprc-tabs__panel" aria-hidden="true">
                                <?php if (!empty($activatedPlugins)) : ?>
                                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p>

                                    <ul class="wpgdprc-list">
                                        <?php
                                        foreach ($activatedPlugins as $key => $plugin) :
                                            $optionName = WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'];
                                            $checked = Helpers::isEnabled($plugin['id']);
                                            $description = (!empty($plugin['description'])) ? apply_filters('the_content', $plugin['description']) : '';
                                            $options = Helpers::getSupportedPluginOptions($plugin['id']);
                                            ?>
                                            <li>
                                                <div class="wpgdprc-checkbox">
                                                    <input type="checkbox" name="<?php echo $optionName; ?>" id="<?php echo $optionName; ?>" value="1" tabindex="1" data-type="save_setting" data-option="<?php echo $optionName; ?>" <?php checked(true, $checked); ?> />
                                                    <label for="<?php echo $optionName; ?>"><?php echo $plugin['name']; ?></label>
                                                    <div class="wpgdprc-switch" aria-hidden="true">
                                                        <div class="wpgdprc-switch-label">
                                                            <div class="wpgdprc-switch-inner"></div>
                                                            <div class="wpgdprc-switch-switch"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if (!empty($description)) : ?>
                                                    <div class="wpgdprc-checklist-description" <?php if (!$checked) : ?>style="display: none;"<?php endif; ?>>
                                                        <?php echo $description; ?>
                                                        <?php echo $options; ?>
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
                                        <?php foreach (Helpers::getSupportedPlugins() as $plugin) : ?>
                                            <li><?php echo $plugin['name']; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p><?php _e('More plugins will be added in the future.', WP_GDPR_C_SLUG); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>

                <p class="wpgdprc-disclaimer"><?php _e('Disclaimer: The creators of this plugin do not have a legal background. We try to assist website and webshop owners in being compliant with the European Unions GDPR law but for rock solid legal advice we recommend contacting a law firm.', WP_GDPR_C_SLUG); ?></p>

                <div class="wpgdprc-features">
                    <div class="wpgdprc-features-inner">
                        <h2><?php _e('Coming soon', WP_GDPR_C_SLUG); ?></h2>
                        <p><?php _e('Tools to automatically comply with GDPR regulations.', WP_GDPR_C_SLUG); ?></p>
                    </div>
                </div>

                <div class="wpgdprc-background"><?php include(WP_GDPR_C_DIR_SVG . '/inline-waves.svg.php'); ?></div>
            </div>
        </div>
        <?php
    }
}