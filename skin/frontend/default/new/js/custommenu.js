function wppShowMenuPopup(objMenu, popupId)
{
    objMenu = $(objMenu.id); var popup = $(popupId); if (!popup) return;
    popup.style.display = 'block';
    objMenu.addClassName('active');
}

function wppPopupPos(objMenu, w)
{
    var pos = objMenu.cumulativeOffset();
    var wraper = $('custommenu');
    var posWraper = wraper.cumulativeOffset();
    var wWraper = wraper.getWidth() - CUSTOMMENU_POPUP_RIGHT_OFFSET_MIN;
    var xTop = pos.top - posWraper.top + CUSTOMMENU_POPUP_TOP_OFFSET;
    var xLeft = pos.left - posWraper.left;
    if ((xLeft + w) > wWraper) xLeft = wWraper - w;
    return {'top': xTop, 'left': xLeft};
}

function wppHideMenuPopup(element, event, popupId, menuId)
{
    element = $(element.id); var popup = $(popupId); if (!popup) return;
    var current_mouse_target = null;
    if (event.toElement)
    {
        current_mouse_target = event.toElement;
    }
    else if (event.relatedTarget)
    {
        current_mouse_target = event.relatedTarget;
    }
    if (!wppIsChildOf(element, current_mouse_target) && element != current_mouse_target)
    {
        if (!wppIsChildOf(popup, current_mouse_target) && popup != current_mouse_target)
        {
            popup.style.display = 'none';
            $(menuId).removeClassName('active');
        }
    }
}

function wppIsChildOf(parent, child)
{
    if (child != null)
    {
        while (child.parentNode)
        {
            if ((child = child.parentNode) == parent)
            {
                return true;
            }
        }
    }
    return false;
}
