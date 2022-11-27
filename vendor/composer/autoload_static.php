<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit657ae3b2e9dad1e4fa78abce044a85e9
{
    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'Yandex\\Translate\\' => 17,
        ),
        'K' => 
        array (
            'Krugozor\\Database\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Yandex\\Translate\\' => 
        array (
            0 => __DIR__ . '/..' . '/yandex/translate-api/src',
        ),
        'Krugozor\\Database\\' => 
        array (
            0 => __DIR__ . '/..' . '/krugozor/database/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit657ae3b2e9dad1e4fa78abce044a85e9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit657ae3b2e9dad1e4fa78abce044a85e9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit657ae3b2e9dad1e4fa78abce044a85e9::$classMap;

        }, null, ClassLoader::class);
    }
}
