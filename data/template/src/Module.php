<?php


namespace __NAMESPACE_PLACEHOLDER__;

class Module
{
    const VERSION = '1.0.0-dev';
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach (glob(__DIR__ . '/../config/*.config.php') as $file) {
            /** @noinspection PhpIncludeInspection */
            $config = array_merge($config, include $file);
        }
        return $config;
    }


}
