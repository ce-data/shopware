<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

require __DIR__ . '/../../autoload.php';

use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Kernel;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class TestKernel extends Kernel
{
    private static TestKernel $kernel;

    /**
     * Static method to start boot kernel without leaving local scope in test helper
     */
    public static function start(): void
    {
        self::$kernel = new self('testing', true);
        self::$kernel->boot();

        $container = self::$kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        $shop = $container->get(ModelManager::class)->getRepository(Shop::class)->getActiveDefault();
        Shopware()->Container()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $_SERVER['HTTP_HOST'] = $shop->getHost();
    }

    public static function getKernel(): TestKernel
    {
        return self::$kernel;
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/config.php';
    }

    protected function prepareContainer(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services_test.xml');
        parent::prepareContainer($container);
    }
}

TestKernel::start();
