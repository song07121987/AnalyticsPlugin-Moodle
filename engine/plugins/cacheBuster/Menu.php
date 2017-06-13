<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\cacheBuster;

use Piwik\Piwik;
use Piwik\Menu\MenuTop;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
		if(Piwik::hasUserSuperUserAccess())
 		{
			$menu->addItem('cacheBuster_cacheBuster', null, $this->urlForDefaultAction(), $orderId = 30);
		}
    }
}
