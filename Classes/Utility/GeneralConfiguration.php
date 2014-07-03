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
 * Description of GeneralConfiguration
 * 
 * @package qm_configuration
 * @subpackage utility
 * @author Guy Couronné <guy.couronne@qcmedia.ca>
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @copyright (c) 2014 Guy Couronné <guy.couronne@qcmedia.ca>
 */
class Tx_QmConfiguration_Utility_GeneralConfiguration implements t3lib_Singleton
{

    /**
     *
     * @var array 
     */
    protected $configuration = array();

    /**
     * Get the configuration for one extension.
     *
     * @param string $extensionName
     * @param int $pageId
     * @param boolean $notEmpty
     * @return array configuration
     */
    public function getConfiguration($extensionName, $pageId = Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID, $notEmpty = true)
    {
        if (!
            $this->hasConfiguration(
                $extensionName,
                $pageId,
                $notEmpty
            )
        ) {
            $extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
            $this->configuration[$extensionName] = array();

            if (!is_array($this->configuration[$extensionName][$pageId])) {
                $this->configuration[$extensionName][$pageId] = array();
            }

            if ($notEmpty === false) {
                $this->configuration[$extensionName][$pageId][$notEmpty] = array_merge_recursive(
                    Tx_QmConfiguration_Utility_Manager_LocalConfiguration::getConfiguration($extensionKey), //Local
                    Tx_QmConfiguration_Utility_Manager_ExtConfiguration::getConfiguration($extensionKey), //extconf
                    Tx_QmConfiguration_Utility_Manager_TypoScriptConfiguration::getConfiguration(
                        $extensionName,
                        $pageId
                    ) //TS
                );
            } else {
                $this->configuration[$extensionName][$pageId][$notEmpty] = array_merge_recursive(
                    $this->arrayNonEmptyItems(Tx_QmConfiguration_Utility_Manager_LocalConfiguration::getConfiguration($extensionKey)), //Local
                    $this->arrayNonEmptyItems(Tx_QmConfiguration_Utility_Manager_ExtConfiguration::getConfiguration($extensionKey)), //extconf
                    $this->arrayNonEmptyItems(
                        Tx_QmConfiguration_Utility_Manager_TypoScriptConfiguration::getConfiguration(
                            $extensionName,
                            $pageId
                        )
                    ) //TS
                );
            }
        }

        return $this->configuration[$extensionName][$pageId][$notEmpty];
    }

    /**
     * Remove the configuration for one extension in this memory.
     * 
     * @param string $extensionName
     * @return \Tx_QmFormation_Utility_TypoScriptConfiguration
     */
    public function removeConfiguration($extensionName)
    {
        if ($this->hasConfiguration($extensionName)) {
            unset($this->configuration[$extensionName]);
        }

        return $this;
    }

    /**
     * 
     * @param string $extensionName
     * @param int $pageId
     * @param boolean $notEmpty
     * @return boolean
     */
    public function hasConfiguration($extensionName, $pageId = Tx_Extbase_Configuration_AbstractConfigurationManager::DEFAULT_BACKEND_STORAGE_PID, $notEmpty = true)
    {
        return (!empty($this->configuration[$extensionName]) && !empty($this->configuration[$extensionName][$pageId]) && !empty($this->configuration[$extensionName][$pageId][$notEmpty]));
    }

    /**
     * 
     * @param type $variable
     * @return array
     */
    public function arrayNonEmptyItems($variable)
    {
        // If it is an element, then just return it
        if (!is_array($variable)) {
            return $variable;
        }

        $non_empty_items = array();
        foreach ($variable as $key => $value) {
            // Ignore empty cells
            if (!$this->isEmpty($value)) {
                // Use recursion to evaluate cells
                $temp = $this->arrayNonEmptyItems($value);
                if (!$this->isEmpty($temp)) {
                    $non_empty_items[$key] = $temp;
                }
            }
        }

        // Finally return the array without empty items
        return $non_empty_items;
    }

    /**
     * 
     * @param type $variable
     * @return boolean
     */
    public function isEmpty($variable)
    {
        // Ignore empty cells
        $isEmpty = true;
        if (isset($variable)) {
            if (is_string($variable) && strlen($variable) > 0) {
                $isEmpty = false;
            } elseif (is_array($variable)) {
                $isEmpty = (empty($variable));
            } elseif (!is_string($variable)) {
                $isEmpty = false;
            }
        }

        return $isEmpty;
    }

}
