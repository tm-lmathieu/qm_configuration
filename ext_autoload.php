<?php

$extensionName = 'qm_configuration';
$extensionPath = t3lib_extMgm::extPath($extensionName);
$extensionClassesPath = $extensionPath . 'Classes/';

$default = array(
    'tx_qmconfiguration_utility_localconfiguration' => $extensionClassesPath . 'Utility/LocalConfiguration.php',
    'tx_qmconfiguration_utility_extconfiguration' => $extensionClassesPath . 'Utility/ExtConfiguration.php',
    'tx_qmconfiguration_utility_typoscriptconfiguration' => $extensionClassesPath . 'Utility/TypoScriptConfiguration.php',
    'tx_qmconfiguration_utility_generalconfiguration' => $extensionClassesPath . 'Utility/GeneralConfiguration.php',
    'tx_qmconfiguration_utility_manager_localconfiguration' => $extensionClassesPath . 'Utility/Manager/LocalConfiguration.php',
    'tx_qmconfiguration_utility_manager_extconfiguration' => $extensionClassesPath . 'Utility/Manager/ExtConfiguration.php',
    'tx_qmconfiguration_utility_manager_typoscriptconfiguration' => $extensionClassesPath . 'Utility/Manager/TypoScriptConfiguration.php',
    'tx_qmconfiguration_utility_manager_generalconfiguration' => $extensionClassesPath . 'Utility/Manager/GeneralConfiguration.php',
);
return $default;
