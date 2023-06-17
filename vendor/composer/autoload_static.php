<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd98a430b29330c84f5b8805a2de63678
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Inc\\Geslib\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Inc\\Geslib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd98a430b29330c84f5b8805a2de63678::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd98a430b29330c84f5b8805a2de63678::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd98a430b29330c84f5b8805a2de63678::$classMap;

        }, null, ClassLoader::class);
    }
}