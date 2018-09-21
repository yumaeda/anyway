// Override NewItemPage.strSite.
NewItemPage.strSite = 'anyway-grapes';


// Override NewItemPage.fnGenerateFormHtml.
NewItemPage.fnGenerateFormHtml['anyway_photos'] = function($div)
{
    NewItemPage.$categorySelect = $(generatePhotoCategorySelectHtml());

    NewItemPage.tryLoadItem();
    $div.html(generatePhotoFormHtml());
};

