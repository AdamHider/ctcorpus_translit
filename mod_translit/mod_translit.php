<?php
      // No direct access
    defined('_JEXEC') or die;
    
    // Include the syndicate functions only once
    require_once dirname(__FILE__) . '/helper.php';
    $lugat_wordlist = [];
    $header_title = '';
    $jinput = JFactory::getApplication()->input;
    
    $language = JFactory::getLanguage();
    $current_language  = $language->getTag();
    require JModuleHelper::getLayoutPath('mod_translit', $params->get('layout', 'default'));
    
    
    
    
    
    
    