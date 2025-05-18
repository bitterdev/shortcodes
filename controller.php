<?php

namespace Concrete\Package\Shortcodes;

use Bitter\Shortcodes\Provider\ServiceProvider;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Package\Package;

final class Controller extends Package
{
    protected string $pkgHandle = 'shortcodes';
    protected $appVersionRequired = '9.0.0';
    protected string $pkgVersion = '1.1.0';
    protected $pkgAutoloaderRegistries = [
        'src/Bitter/Shortcodes' => '\Bitter\Shortcodes',
    ];

    public function getPackageName(): string
    {
        return t('Shortcodes');
    }

    public function getPackageDescription(): string
    {
        return t('Replaces shortcodes with text replacements.');
    }

    public function on_start()
    {
        /** @var ServiceProvider $provider */
        /** @noinspection PhpUnhandledExceptionInspection */
        $provider = $this->app->make(ServiceProvider::class);
        $provider->register();
    }

    public function install()
    {
        parent::install();
        $this->installContentFile("data.xml");
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile("data.xml");
    }

    public function uninstall()
    {
        parent::uninstall();

        /** @var Connection $db */
        /** @noinspection PhpUnhandledExceptionInspection */
        $db = $this->app->make(Connection::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $db->executeQuery('DROP TABLE IF EXISTS ShortcodesUsage');
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $db->executeQuery('DROP TABLE IF EXISTS ShortcodesEntries');
    }
}
