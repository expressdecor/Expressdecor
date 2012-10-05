/*
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Followupemail
 * @copyright  Copyright (c) 2008-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
*/

function openGridRow(grid, event)
{
    var element = Event.findElement(event, 'tr');

    if(['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase())!=-1)
        return;

    if(element.title)
    {
        var win = window.open(element.title, 'followuppreview', 'width=600,height=400,resizable=1,scrollbars=1');
        win.focus();
        Event.stop(event);
    }
}
