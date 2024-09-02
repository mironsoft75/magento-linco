<?php
namespace Lyracons\MegaMenu\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{

    /**
     * @var Setup\SampleData\Executor
     */
    private $menu;

    public function __construct(
        \Lyracons\MegaMenu\Model\MenuData $menu
    )
    {
        $this->menu = $menu;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->menu->install([
            'Lyracons_MegaMenu::fixtures/lyracons_megamenu.csv',
        ]);
    }
}
