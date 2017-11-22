<?php

/**
 * gcalparser build script
 *
 * @package gcalparser
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define package */
define('PKG_NAME', 'gcalparser');
define('PKG_NAMESPACE', 'gcalparser');
define('PKG_VERSION', '0.0.1');
define('PKG_RELEASE', 'beta');
define('PKG_CATEGORY', 'gcalparser');

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';

$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'install_options' => $root . '_build/install.options/',
    'resolvers' => $root . '_build/resolvers/',
    'docs' => $root . 'core/components/' . PKG_NAMESPACE . '/docs/',
    'source_core' => $root . 'core/components/' . PKG_NAMESPACE,
);
unset($root);
/* load modx */
require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
echo XPDO_CLI_MODE ? '' : '<pre>';
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAMESPACE, false, true, '{core_path}components/' . PKG_NAMESPACE . '/');



/** System settings * */
$settings = include_once $sources['data'] . 'transport.settings.php';
$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'key',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
);
if (!is_array($settings)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding settings failed.');
}
foreach ($settings as $setting) {
    $vehicle = $builder->createVehicle($setting, $attributes);
    $builder->putVehicle($vehicle);
}
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' system settings.');
flush();
unset($settings, $setting, $attributes);

/* add snippet */
$snippet = include $sources['data'] . 'transport.snippet.php';
if (!is_array($snippet)) {
    $modx->log(modX::LOG_LEVEL_FATAL, 'Adding snippet failed.');
}
$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(

    ),
);
foreach ($snippet as $code) {
    $vehicle = $builder->createVehicle($code, $attributes);
    $builder->putVehicle($vehicle);
}
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippet) . ' snippet.');
flush();
unset($snippet, $code, $attributes);



/* create category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in category.');
flush();

$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true);
$vehicle = $builder->createVehicle($category, $attr);


$modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers...');
$vehicle->resolve('php', array(
    'source' => $sources['resolvers'] . 'install.script.php',
));

$modx->log(modX::LOG_LEVEL_INFO, 'Adding in FILE resolvers...');
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in resolvers.');
flush();

$builder->putVehicle($vehicle);

/* load system settings */


/* now pack in the license file, readme and setup options */
$modx->log(xPDO::LOG_LEVEL_INFO, 'Setting Package Attributes...');
flush();
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
        )
);


$modx->log(xPDO::LOG_LEVEL_INFO, 'Zipping up package...');
flush();
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit();