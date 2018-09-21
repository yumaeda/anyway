function resizeImage()
{
    var imgWidth = $('body').width() / 10;
    $('img.instagramImg').width(imgWidth);
}

$(document).ready(function()
{
    $.ajax({
        url: './instagram_request.php',
        type: 'POST',
        dataType: 'json',
        error: function() {},
        success: function(jsonResponse)
        {
            var html     = '',
                rgobjImg = jsonResponse.data,
                cImg     = rgobjImg.length,
                objImg   = null;

            for (var i = 0; i < cImg; ++i)
            {
                objImg = rgobjImg[i];
                html += '<a href="' + objImg.link + '" target="_blank"><img class="instagramImg" src="' + objImg.images.low_resolution.url + '" /></a>';
            }

            $('div.instagram').html(html);
            resizeImage();
        }
    });

    $(window).resize(function()
    {
        resizeImage();
        $('iframe#wholesale').attr('src', $('iframe#wholesale').attr('src'));
        $('iframe#access-map').attr('src', $('iframe#access-map').attr('src'));
    });

    $('div.contents').click(function()
    {
        if ($('header').is(':hidden'))
        {
            $('header').fadeIn(500);
        }

        return false;
    });

    $('a.onlineStoreLnk').hover(function()
    {
        $(this).html('オンラインショップ');
    }, 
    function()
    {
        $(this).html('Online Store');
    });

    $('a.fbLnk').hover(function()
    {
        $(this).html('フェイスブック');
    }, 
    function()
    {
        $(this).html('Facebook');
    });

    $('a.blogLnk').hover(function()
    {
        $(this).html('ブログ');
    }, 
    function()
    {
        $(this).html('Blog');
    });

    $('a.mailMagazineLnk').hover(function()
    {
        $(this).html('メルマガ');
    }, 
    function()
    {
        $(this).html('Mail Magazine');
    });

    $('a.accessLnk').hover(function()
    {
        $(this).html('アクセスマップ');
    }, 
    function()
    {
        $(this).html('Access');
    });

    $('a.accessLnk').click(function()
    {
        $('html,body').animate({ scrollTop: $('iframe#access-map').offset().top }, { queue : false });
        $('header').fadeOut(500);
        $('iframe#wholesale').fadeOut(500);

        return false;
    });

    $('a.wholesaleLnk').hover(function()
    {
        $(this).html('業務販売');
    }, 
    function()
    {
        $(this).html('Wholesale');
    });

    $('a.wholesaleLnk').click(function()
    {
        var $iframe = $('iframe#wholesale').fadeIn(500);
        $iframe.fadeIn(500);

        $('html,body').animate({ scrollTop: $iframe.offset().top }, { queue : false });
        $('header').fadeOut(500);

        return false;
    });

    $('div#adPane').click('tasting-link', function()
    {
        location.href = 'http://anyway-grapes.jp/store/index.php?pc_view=1&submenu=photo&type=13';
    });
});

