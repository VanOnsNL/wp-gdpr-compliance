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
            register_setting(WP_GDPR_C_SLUG . '_general', WP_GDPR_C_PREFIX . '_general_' . $id, 'intval');
        }
        register_setting(WP_GDPR_C_SLUG . '_settings', WP_GDPR_C_PREFIX . '_settings_privacy_policy_page', 'intval');
        register_setting(WP_GDPR_C_SLUG . '_settings', WP_GDPR_C_PREFIX . '_settings_privacy_policy_text', array('sanitize_callback' => array(Helpers::getInstance(), 'sanitizeData')));
        register_setting(WP_GDPR_C_SLUG . '_settings', WP_GDPR_C_PREFIX . '_settings_enable_access_request', 'intval');
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
        $type = (isset($_REQUEST['type'])) ? esc_html($_REQUEST['type']) : false;
        $pluginData = Helpers::getPluginData();
        $daysLeftToComply = Helpers::getDaysLeftToComply();
        $enableAccessRequest = Helpers::isEnabled('enable_access_request', 'settings');
        $adminUrl = Helpers::getPluginAdminUrl();
        ?>
        <div class="wrap">
            <div class="wpgdprc">
                <h1 class="wpgdprc-title"><span class="dashicons dashicons-lock"></span> <?php echo $pluginData['Name']; ?></h1>

                <?php settings_errors(); ?>

                <div class="wpgdprc-navigation wpgdprc-clearfix">
                    <a class="<?php echo (empty($type)) ? 'active' : ''; ?>" href="<?php echo $adminUrl; ?>"><?php _e('Integrations', WP_GDPR_C_SLUG); ?></a>
                    <?php if ($enableAccessRequest) : ?>
                        <a class="<?php echo checked('requests', $type, false) ? 'active' : ''; ?>" href="<?php echo $adminUrl; ?>&type=requests"><?php _e('Requests', WP_GDPR_C_SLUG); ?></a>
                    <?php endif; ?>
                    <a class="<?php echo checked('checklist', $type, false) ? 'active' : ''; ?>" href="<?php echo $adminUrl; ?>&type=checklist"><?php _e('Checklist', WP_GDPR_C_SLUG); ?></a>
                    <a class="<?php echo checked('settings', $type, false) ? 'active' : ''; ?>" href="<?php echo $adminUrl; ?>&type=settings"><?php _e('Settings', WP_GDPR_C_SLUG); ?></a>
                </div>

                <div class="wpgdprc-content wpgdprc-clearfix">
                    <?php
                    switch ($type) {
                        case 'requests' :
                            $id = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;
                            if (!empty($id) && AccessRequest::getInstance()->exists($id)) {
                                self::renderManageRequestPage($id);
                            } else {
                                self::renderRequestsPage();
                            }
                            break;
                        case 'checklist' :
                            self::renderChecklistPage();
                            break;
                        case 'settings' :
                            self::renderSettingsPage();
                            break;
                        default :
                            self::renderIntegrationsPage();
                            break;
                    }
                    ?>
                </div>

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

    private static function renderIntegrationsPage() {
        $pluginData = Helpers::getPluginData();
        $activatedPlugins = Helpers::getActivatedPlugins();
        ?>
        <form method="post" action="<?php echo admin_url('options.php'); ?>" novalidate="novalidate">
            <?php settings_fields(WP_GDPR_C_SLUG . '_integrations'); ?>
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
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Page: Checklist
     */
    private static function renderChecklistPage() {
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
     * Page: Settings
     */
    private static function renderSettingsPage() {
        $optionNamePrivacyPolicyPage = WP_GDPR_C_PREFIX . '_settings_privacy_policy_page';
        $optionNamePrivacyPolicyText = WP_GDPR_C_PREFIX . '_settings_privacy_policy_text';
        $optionNameEnableAccessRequest = WP_GDPR_C_PREFIX . '_settings_enable_access_request';
        $privacyPolicyPage = get_option($optionNamePrivacyPolicyPage);
        $privacyPolicyText = esc_html(Integrations::getPrivacyPolicyText());
        $enableAccessRequest = Helpers::isEnabled('enable_access_request', 'settings');
        ?>
        <p><?php _e('Use %privacy_policy% if you want to link your Privacy Policy page in the GDPR checkbox texts.', WP_GDPR_C_SLUG); ?></p>
        <p><strong><?php _e('Privacy Policy', WP_GDPR_C_SLUG); ?></strong></p>
        <form method="post" action="<?php echo admin_url('options.php'); ?>" novalidate="novalidate">
            <?php settings_fields(WP_GDPR_C_SLUG . '_settings'); ?>
            <div class="wpgdprc-setting">
                <label for="<?php echo $optionNamePrivacyPolicyPage; ?>"><?php _e('Privacy Policy', WP_GDPR_C_SLUG); ?></label>
                <div class="wpgdprc-options">
                    <?php
                    wp_dropdown_pages(array(
                        'show_option_none' => __('Select an option', WP_GDPR_C_SLUG),
                        'name' => $optionNamePrivacyPolicyPage,
                        'selected' => $privacyPolicyPage
                    ));
                    ?>
                </div>
            </div>
            <div class="wpgdprc-setting">
                <label for="<?php echo $optionNamePrivacyPolicyText; ?>"><?php _e('Link text', WP_GDPR_C_SLUG); ?></label>
                <div class="wpgdprc-options">
                    <input type="text" name="<?php echo $optionNamePrivacyPolicyText; ?>" class="regular-text" id="<?php echo $optionNamePrivacyPolicyText; ?>" placeholder="<?php echo $privacyPolicyText; ?>" value="<?php echo $privacyPolicyText; ?>" />
                </div>
            </div>
            <p><strong><?php _e('Request User Data', WP_GDPR_C_SLUG); ?></strong></p>
            <div class="wpgdprc-setting">
                <label for="<?php echo $optionNameEnableAccessRequest; ?>"><?php _e('Enable', WP_GDPR_C_SLUG); ?></label>
                <div class="wpgdprc-options">
                    <label><input type="checkbox" name="<?php echo $optionNameEnableAccessRequest; ?>" id="<?php echo $optionNameEnableAccessRequest; ?>" value="1" tabindex="1" data-type="save_setting" data-option="<?php echo $optionNameEnableAccessRequest; ?>" <?php checked(true, $enableAccessRequest); ?> /> <?php _e('Yes', WP_GDPR_C_SLUG); ?></label>
                    <div class="wpgdprc-information">
                        <?php
                        printf(
                            '<strong>%s:</strong> %s',
                            strtoupper(__('Note', WP_GDPR_C_SLUG)),
                            __('Enabling this will create a page with...', WP_GDPR_C_SLUG)
                        );
                        ?>
                    </div>
                </div>
            </div>
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * @param int $requestId
     */
    private static function renderManageRequestPage($requestId = 0) {
        $accessRequest = new AccessRequest($requestId);
        $filters = array(
            'access_request_id' => array(
                'value' => $accessRequest->getId(),
            ),
        );
        $paged = (isset($_REQUEST['paged'])) ? absint($_REQUEST['paged']) : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;
        $total = DeleteRequest::getInstance()->getTotal($filters);
        $numberOfPages = ceil($total / $limit);
        $requests = DeleteRequest::getInstance()->getList($filters, $limit, $offset);
        if (!empty($requests)) :
            ?>
            <div class="wpgdrc-message wpgdrc-message--notice">
                <strong>Information</strong>
                <div class="wpgdprc__message">
                    <p>Process a request by selecting it and pressing the button below.</p>
                </div>
            </div>

            <form class="wpgdprc-form wpgdprc-form--process-delete-requests" method="POST" novalidate="novalidate">
                <table class="wpgdprc-table">
                    <thead>
                    <tr>
                        <th scope="col" width="10%"><?php _e('ID', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="10%"><?php _e('Data ID', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="18%"><?php _e('Type', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="18%"><?php _e('IP Address', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="22%"><?php _e('Date', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="12%"><?php _e('Processed', WP_GDPR_C_SLUG); ?></th>
                        <th scope="col" width="10%">
                            <input type="checkbox" class="wpgdprc-select-all" />
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /** @var DeleteRequest $request */
                    foreach ($requests as $request) :
                        ?>
                        <tr data-id="<?php echo $request->getType(); ?>-<?php echo $request->getDataId(); ?>">
                            <td><?php printf('#%d', $request->getId()); ?></td>
                            <td><?php printf('#%d', $request->getDataId()); ?></td>
                            <td><?php echo $request->getType(); ?></td>
                            <td><?php echo $request->getIpAddress(); ?></td>
                            <td><?php echo $request->getDateCreated(); ?></td>
                            <td><span class="dashicons dashicons-<?php echo ($request->getProcessed()) ? 'yes' : 'no'; ?>"></span></td>
                            <td>
                                <input type="checkbox" class="wpgdprc-checkbox" value="<?php echo $request->getDataId(); ?>" data-type="<?php echo $request->getType(); ?>" /> | <?php printf('<a target="_blank" href="%s">%s</a>', $request->getManageUrl(), __('View', WP_GDPR_C_SLUG)); ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <?php submit_button(__('Process selected request(s)', WP_GDPR_C_SLUG), 'primary wpgdprc-remove'); ?>
            </form>

            <div class="wpgdprc-pagination">
                <?php
                    echo paginate_links(array(
                        'base' => str_replace(
                            999999999,
                            '%#%',
                            add_query_arg(
                                array('paged' => 999999999),
                                Helpers::getPluginAdminUrl()
                            )
                        ),
                        'format' => '?paged=%#%',
                        'current' => max(1, $paged),
                        'total' => $numberOfPages,
                        'prev_text' => '&lsaquo;',
                        'next_text' => '&rsaquo;',
                        'before_page_number' => '<span>',
                        'after_page_number' => '</span>'
                    ));
                    printf('<span class="wpgdprc-pagination__results">%s</span>', sprintf(__('%d of %d results found', WP_GDPR_C_SLUG), count($requests), $total));
                ?>
            </div>
            <?php
        else :
            ?>
            <p><strong><?php _e('No delete requests found.', WP_GDPR_C_SLUG); ?></strong></p>
            <?php
        endif;
        ?>
        <?php
    }

    /**
     * Page: Requests
     */
    private static function renderRequestsPage() {
        $paged = (isset($_REQUEST['paged'])) ? absint($_REQUEST['paged']) : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;
        $total = AccessRequest::getInstance()->getTotal();
        $numberOfPages = ceil($total / $limit);
        $requests = AccessRequest::getInstance()->getList(array(), $limit, $offset);
        if (!empty($requests)) :
            ?>
            <table class="wpgdprc-table">
                <thead>
                <tr>
                    <th scope="col" width="10%"><?php _e('ID', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="20%"><?php _e('Requests to Process', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="22%"><?php _e('Email Address', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="18%"><?php _e('IP Address', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="22%"><?php _e('Date', WP_GDPR_C_SLUG); ?></th>
                    <th scope="col" width="8%"><?php _e('Active', WP_GDPR_C_SLUG); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var AccessRequest $request */
                foreach ($requests as $request) :
                    $amountOfDeleteRequests = DeleteRequest::getInstance()->getAmountByAccessRequestId($request->getId());
                    $url = add_query_arg(
                        array('id' => $request->getId()),
                        sprintf(
                            admin_url('tools.php?page=%s&type=requests'),
                            str_replace('-', '_', WP_GDPR_C_SLUG)
                        )
                    );
                    ?>
                    <tr>
                        <td><?php printf('#%d', $request->getId()); ?></td>
                        <td>
                            <?php printf('%d', $amountOfDeleteRequests); ?>
                            <?php
                            if ($amountOfDeleteRequests > 0) {
                                printf('<a href="%s">%s</a>', $url, __('Manage', WP_GDPR_C_SLUG));
                            }
                            ?>
                        </td>
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
            <div class="wpgdprc-pagination">
                <?php
                echo paginate_links(array(
                    'base' => str_replace(
                        999999999,
                        '%#%',
                        add_query_arg(
                            array('paged' => 999999999),
                            Helpers::getPluginAdminUrl()
                        )
                    ),
                    'format' => '?paged=%#%',
                    'current' => max(1, $paged),
                    'total' => $numberOfPages,
                    'prev_text' => '&lsaquo;',
                    'next_text' => '&rsaquo;',
                    'before_page_number' => '<span>',
                    'after_page_number' => '</span>'
                ));
                printf('<span class="wpgdprc-pagination__results">%s</span>', sprintf(__('%d of %d results found', WP_GDPR_C_SLUG), count($requests), $total));
                ?>
            </div>
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