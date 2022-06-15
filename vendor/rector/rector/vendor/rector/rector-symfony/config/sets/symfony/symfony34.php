<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector::class)->configure([new \Rector\Removing\ValueObject\ArgumentRemover('Symfony\\Component\\Yaml\\Yaml', 'parse', 2, ['Symfony\\Component\\Yaml\\Yaml::PARSE_KEYS_AS_STRINGS'])]);
    $services->set(\Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector::class);
};
