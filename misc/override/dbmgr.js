// Override DBManagerPage.strSite.
DBManagerPage.strSite = 'anyway-grapes';

// Override DBManagerPage.fnAddMenus().
DBManagerPage.fnAddMenus = function(menuList)
{
    DBManagerPage._addMenu(menuList, { id: 'whatsNewLnk', href: '#', resId: 'WHATS_NEW_STR' });
};

DBManagerPage.fnSetDatabase['whatsNewLnk'] = function()
{
    DBManagerPage.dbTable         = 'anyway_new_info';
    DBManagerPage.strSearchKey    = 'year';
    DBManagerPage.$categorySelect = $(generateYearSelectHtml(Strings.getString('EVENT_YEAR_STR'), 2014));
};

// Override DBManagerPage.fnGenerateTableRowHtml.
DBManagerPage.fnGenerateTableRowHtml['eventCategorySelect'] = function(objEvent)
{
    var html =
        '<tr class="dbmgrRow">' +
            '<td class="checkBoxCol">' +
                DBManagerPage._generateCheckBoxHtml(objEvent) +
            '</td>' +
            '<td class="mediumText">' +
                '{0}/{1}'.format(objEvent.month, objEvent.date) +
            '</td>' +
            '<td class="smallText">' +
                objEvent.description +
            '</td>' +
        '</tr>';

    return html;
};

DBManagerPage.fnGenerateTableRowHtml['newWineColorSelect'] = function(objWine)
{
    var html =
        '<tr class="dbmgrRow">' +
            '<td class="checkBoxCol">' +
                DBManagerPage._generateCheckBoxHtml(objWine) +
            '</td>' +
            '<td class="mediumText">' +
                WineUtility.getVintageText(objWine) +
            '</td>' +
            '<td class="mediumText">' +
                objWine.name +
            '</td>' +
            '<td class="smallText">' +
                objWine.name_jpn +
            '</td>' +
            '<td class="mediumText">' +
                objWine.producer +
            '</td>' +
            '<td class="smallText">' +
                objWine.producer_jpn +
            '</td>' +
        '</tr>';

    return html;
};

