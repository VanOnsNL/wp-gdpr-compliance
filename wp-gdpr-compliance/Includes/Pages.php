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
        $checklist = array(
            'contact_form' => array(
                'label' => 'Do you have a contact form?',
                'description' => 'Make sure you add a checkbox specifically asking the user of the form if they consent to you storing and using their personal information to get back in touch with them. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.'
            ),
            'comments' => array(
                'label' => 'Can visitors comment anywhere on your website?',
                'description' => 'Make sure you add a checkbox specifically asking the user of the comment section if they consent to storing their message attached to the e-mail address they\'ve used to comment. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.'
            ),
            'webshop' => array(
                'label' => 'Is there an order form on your website or webshop present?',
                'description' => 'Make sure you add a checkbox specifically asking the user of the form if they consent to you storing and using their personal information to ship the order. This cannot be the same checkbox as the Privacy Policy checkbox you should already have in place. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.'
            ),
            'forum' => array(
                'label' => 'Do you provide a forum or message board environment?',
                'description' => 'Make sure you add a checkbox specifically asking forum / board users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.'
            ),
            'chat' => array(
                'label' => 'Can visitors chat with your company directly?',
                'description' => 'Make sure you add a checkbox specifically asking chat users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. We recommend also mentioning for how long you will store chat messages or deleting them all within 24 hours. Also mention if you will send or share the data with any 3rd-parties and which.'
            ),
        );
        ?>
        <div class="wrap">
            <div class="wpgdprc">
                <h1 class="wpgdprc-title"><i class="fa fa-lock" aria-hidden="true"></i> <?php _e('WP GDPR Compliance', WP_GDPR_C_SLUG); ?></h1>

                <p class="wpgdprc-description">
                    This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR).
                    By May 24th, 2018 your website or shop has to comply to avoid large fines. The regulation can be read here:
                    <a target="_blank" href="//www.eugdpr.org/the-regulation.html">GDPR Key Changes</a></p>
                <p>Below we ask you what private data you currently collect and provide you with tips to comply.</p>

                <?php if (!empty($checklist)) : ?>
                <ul class="wpgdprc-checklist">
                    <?php foreach ($checklist as $id => $check) : ?>
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
                <?php endif; ?>

                <p class="wpgdprc-disclaimer"><?php _e('The creators of this plugin do not have a legal background. We try to assist website and webshop owners in being compliant with the European Unions GDPR law but for rock solid legal advice we recommend contacting a law firm.', WP_GDPR_C_SLUG); ?></p>

                <div class="wpgdprc-background"><?php include(WP_GDPR_C_DIR_SVG . '/inline-waves.svg.php'); ?></div>
            </div>
        </div>
        <?php
    }
}