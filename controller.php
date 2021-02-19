<?php

namespace Concrete\Package\Shortcodes;

use A3020\Shortcodes\Installer\Installer;
use A3020\Shortcodes\ShortcodesServiceProvider;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Package as PackageFacade;

final class Controller extends Package
{
    protected $pkgHandle = 'shortcodes';
    protected $appVersionRequired = '8.0.0';
    protected $pkgVersion = '1.0.1';
    protected $pkgAutoloaderRegistries = [
        'src/Shortcodes' => '\A3020\Shortcodes',
    ];

    public function getPackageName()
    {
        return t('Shortcodes');
    }

    public function getPackageDescription()
    {
        return t('Replaces shortcodes with text replacements.');
    }

    public function on_start()
    {
        $provider = $this->app->make(ShortcodesServiceProvider::class);
        $provider->register();
    }

    public function install()
    {
        $pkg = parent::install();

        $installer = $this->app->make(Installer::class);
        $installer->install($pkg);
    }

    public function upgrade()
    {
        parent::upgrade();

        /** @see \Concrete\Core\Package\PackageService */
        $pkg = PackageFacade::getByHandle($this->pkgHandle);

        /** @var Installer $installer */
        $installer = $this->app->make(Installer::class);
        $installer->install($pkg);
    }

    public function uninstall()
    {
        parent::uninstall();

        $db = $this->app->make(Connection::class);
        $db->executeQuery('DROP TABLE IF EXISTS ShortcodesUsage');
        $db->executeQuery('DROP TABLE IF EXISTS ShortcodesEntries');
    }
}
