<?php

namespace WPGDPRC\Includes;

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

    public function addAdminMenu() {
        add_submenu_page(
            'tools.php',
            __('WP GDPR Compliance', WP_GDPR_C_SLUG),
            __('WP GDPR Compliance', WP_GDPR_C_SLUG),
            'manage_options',
            'wp_gdpr_compliance',
            array($this, 'generatePage')
        );
    }

    public function generatePage() {
        ?>
        <div class="wrap">
            <div class="wpgdprc">
                <h1 class="wpgdprc-title"><i class="fa fa-lock" aria-hidden="true"></i> <?php _e('WP GDPR Compliance', WP_GDPR_C_SLUG); ?></h1>

                <p class="wpgdprc-description">
                    This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR).
                    By May 24th, 2018 your website or shop has to comply to avoid large fines. The regulation can be read here:
                    <a target="_blank" href="//www.eugdpr.org/the-regulation.html">GDPR Key Changes</a></p>
                <p>Below we ask you what private data you currently collect and provide you with tips to comply.</p>

                <ul class="wpgdprc-checklist">
                    <?php foreach (Helpers::getCheckList() as $id => $check) : ?>
                    <li>
                        <div class="wpgdprc-checkbox">
                            <input type="checkbox" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="" tabindex="1" />
                            <label for="<?php echo $id; ?>"><?php echo $check['label']; ?></label>
                            <div class="wpgdprc-switch" aria-hidden="true">
                                <div class="wpgdprc-switch-label">
                                    <div class="wpgdprc-switch-inner"></div>
                                    <div class="wpgdprc-switch-switch"></div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($check['description'])) : ?>
                            <div class="wpgdprc-checklist-description" style="display: none;">
                                <?php echo esc_html($check['description']); ?>
                            </div>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

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