<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Xavi Torello - http://xaviertorello.cat
 *
 */

namespace Piwik\Plugins\kDebug;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {


        $menu->add('General_Settings', 'kDebug_Debug',
                   array('module' => 'kDebug', 'action' => 'index'),
                   Piwik::hasUserSuperUserAccess(),
                   $order = 7);
    }


/* ToDo in the next release - Integrate js/css load using PW defaults
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = '';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] ='';
    }
*/



}
