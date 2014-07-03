<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Guy Couronné <guy.couronne@qcmedia.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Description of TypoScriptConfiguration
 * 
 * @package qm_configuration
 * @subpackage utility
 * @author Guy Couronné <guy.couronne@qcmedia.ca>
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @copyright (c) 2014 Guy Couronné <guy.couronne@qcmedia.ca>
 */
class Tx_QmConfiguration_Utility_TypoScriptConfiguration implements t3lib_Singleton
{

    /**
     * @var array
     */
    protected $typoScriptSetupCache = array();

    /**
     *
     * @var Tx_Extbase_Configuration_ConfigurationManagerInterface 
     */
    protected $configurationManager;

    /**
     * @param Tx_Extbase_Object_ObjectManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Get the typoscript configuration for one extension.
     *
     * @param string $extensionName
     * @return array typoscript configuration
     */
    public function getConfiguration($extensionName, $pageId = Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID)
    {
        return
            $this->parseConfiguration(
                $extensionName,
                $pageId
        );
    }

    /**
     * Parse typoscript and return it as array
     * 
     * @param string $extensionName
     * @return array
     */
    protected function parseConfiguration($extensionName, $pageId = Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID)
    {
        $configuration = $this->configurationManager->getConfiguration(
            Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $extensionName,
            ''
        );

        //Backend TS
        $extensionNameModule = 'tx_' . strtolower($extensionName) . '.';
        $configurationTS = array();

        $pageId = (int) $pageId;
        $pageId = ($pageId > 0) ? $pageId : Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID;


        //If pageId specified, get it from there
        if ($pageId != Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID) {
            //If not get it from new pageId
            $setup = $this->getTypoScriptSetup($pageId);
            if (is_array($setup['module.'][$extensionNameModule])) {
                //TYPO3 version check
                if (class_exists('Tx_Extbase_Utility_TypoScript')) {
                    $configurationTS = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                } elseif (class_exists('Tx_Extbase_Service_TypoScriptService')) {
                    $objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
                    $typoScriptService = $objectManager->get('Tx_Extbase_Service_TypoScriptService');

                    $configurationTS = $typoScriptService->convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                }

                $configuration = array_merge(
                    $configuration,
                    $configurationTS
                );
            }
        }

        //check if TS is good
        if (empty($configuration['settings'])) {
            //If not get it from new pageId
            $setup = $this->getTypoScriptSetup($pageId);
            if (is_array($setup['module.'][$extensionNameModule])) {
                //TYPO3 version check
                if (class_exists('Tx_Extbase_Utility_TypoScript')) {
                    $configurationTS = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                } elseif (class_exists('Tx_Extbase_Service_TypoScriptService')) {
                    if (empty($typoScriptService)) {
                        $objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
                        $typoScriptService = $objectManager->get('Tx_Extbase_Service_TypoScriptService');
                    }

                    $configurationTS = $typoScriptService->convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                }

                $configuration = array_merge(
                    $configuration,
                    $configurationTS
                );
            }

            if (empty($configuration['settings']) && !empty($GLOBALS['TSFE']) && !empty($GLOBALS['TSFE']->tmpl)) {
                //If not get it from TSFE
                $setup = $GLOBALS['TSFE']->tmpl->setup;
                if (is_array($setup['module.'][$extensionNameModule])) {
                    //TYPO3 version check
                    if (class_exists('Tx_Extbase_Utility_TypoScript')) {
                        $configurationTS = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                    } elseif (class_exists('Tx_Extbase_Service_TypoScriptService')) {
                        if (empty($typoScriptService)) {
                            $objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
                            $typoScriptService = $objectManager->get('Tx_Extbase_Service_TypoScriptService');
                        }

                        $configurationTS = $typoScriptService->convertTypoScriptArrayToPlainArray($setup['module.'][$extensionNameModule]);
                    }

                    $configuration = array_merge(
                        $configuration,
                        $configurationTS
                    );
                }
            }
        }

        if (!is_array($configuration)) {
            $configuration = array();
        }

        return $configuration;
    }

    /**
     * Returns TypoScript Setup array from current Environment.
     *
     * @param int $pageId
     * @return array the raw TypoScript setup
     */
    protected function getTypoScriptSetup($pageId)
    {
        if (
            !array_key_exists(
                $pageId,
                $this->typoScriptSetupCache
            )
        ) {
            $template = t3lib_div::makeInstance('t3lib_TStemplate');
            // do not log time-performance information
            $template->tt_track = 0;
            $template->init();
            // Get the root line
            $sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
            // get the rootline for the current page
            $rootline = $sysPage->getRootLine($pageId);
            // This generates the constants/config + hierarchy info for the template.
            $template->runThroughTemplates(
                $rootline,
                0
            );
            $template->generateConfig();
            $this->typoScriptSetupCache[$pageId] = $template->setup;
        }
        return $this->typoScriptSetupCache[$pageId];
    }

}
