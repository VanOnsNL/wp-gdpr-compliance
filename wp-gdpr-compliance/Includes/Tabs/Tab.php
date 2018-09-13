<?php

namespace WPGDPRC\Includes\Tabs;

use WPGDPRC\Includes\Helper;

class Tab implements TabInterface {

	private $className;
	private $tabUrl;

	public function __construct($tabName) {
		if(empty($tabName)):
			return; // TODO: Add Exception.
		endif;
		$this->tabName = $tabName;
		$this->type = strtolower($this->tabName);
		$this->translatedTabName = __($this->tabName, WP_GDPR_C_SLUG);
		$this->request = (isset($_REQUEST['type'])) ? esc_html($_REQUEST['type']) : false;
		$this->adminUrl = Helper::getPluginAdminUrl();
		$this->getClassName();
		$this->createTab();
		$this->checkFilters();
	}

	public function buildTabUrl() {
		$this->tabUrl = $this->adminUrl.'&type='. $this->type;
		return $this->tabUrl;
	}

	public function getClassName() {

		// Check if we're on the tab to highlight it.
		if(strtolower($this->request) == strtolower($this->type)):
			$this->className = 'wpgdprc-active';
		endif;
	}

	public function createTab() {
		$output = '<a class="' . $this->className .'" href="'.$this->buildTabUrl().'">'. $this->translatedTabName .'</a>';
		echo $output;

	}

}