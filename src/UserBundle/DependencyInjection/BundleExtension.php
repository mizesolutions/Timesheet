<?php
/**
 * Created by PhpStorm.
 * User: kenfarr
 * Date: 6/24/16
 * Time: 10:09 PM
 */

#src/UserBundle/DependencyInjection/BundleExtension.php

$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
$loader->load('services.yml');