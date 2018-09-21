// Override DBManagerPage.strSite.
DBManagerPage.strSite = 'anyway-grapes';

// Override DBManagerPage.fnAddMenus().
DBManagerPage.fnAddMenus = function(menuList)
{
    DBManagerPage._addMenu(menuList, { id: 'photoLnk', href: '#', resId: 'PHOTO_STR' });
};

// Override DBManagerPage.fnSetDatabase.
DBManagerPage.fnSetDatabase['photoLnk'] = function()
{
    DBManagerPage.dbTable = 'anyway_photos';
    DBManagerPage.$categorySelect = $(generatePhotoCategorySelectHtml());
};

