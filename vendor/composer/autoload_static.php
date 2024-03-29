<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9d6292a411b13871b40a0fab2d50620e
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'EtherpadPlugin\\' => 15,
            'EtherpadLite\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'EtherpadPlugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
        'EtherpadLite\\' => 
        array (
            0 => __DIR__ . '/..' . '/tomnomnom/etherpad-lite-client/EtherpadLite',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9d6292a411b13871b40a0fab2d50620e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9d6292a411b13871b40a0fab2d50620e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9d6292a411b13871b40a0fab2d50620e::$classMap;

        }, null, ClassLoader::class);
    }
}
