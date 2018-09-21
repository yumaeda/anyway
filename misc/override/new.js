// Override NewItemPage.strSite.
NewItemPage.strSite = 'anyway-grapes';

NewItemPage.fnGenerateFormHtml['anyway_new_info'] = function($div)
{
    NewItemPage.$categorySelect = $(generateYearSelectHtml(Strings.getString('EVENT_YEAR_STR'), 2014));

    NewItemPage.tryLoadItem();
    $div.html(generateEventFormHtml());
};

