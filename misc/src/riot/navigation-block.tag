<navigation-block>

<div class="navigation-pane">
    <span each="{ link in links }">
        <a href="{ link.url }">{ link.text }</a>
        <span if="{ link.text != 'Mail Magazine' }">
            &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        </span>
    </span>
</div>

this.links = [
    { url: 'http://anyway-grapes.jp/store',                                                    text: 'Online Store' },
    { url: 'https://www.facebook.com/WINE-SHOP-Anyway-Grapes-500910809965433/',                text: 'Facebook' },
    { url: 'http://anyway-grapes.jp/blog',                                                     text: 'Blog' },
    { url: 'http://us8.campaign-archive2.com/home/?u=0ad89bde4937b5d71a1175566&id=136d202046', text: 'Mail Magazine' },
]

</navigation-block>

