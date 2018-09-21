<header-block>

<div class="header-pane">
    <ul>
        <li each="{ anchor in anchors }">
            <a href="{ anchor.url }" class="{ anchor.class }">{ anchor.text }</a>
        </li>
    </ul>
</div>

this.anchors = [
    { class: 'onlineStoreLnk',  text: 'Online Store',  url: 'http://anyway-grapes.jp/store' },
    { class: 'fbLnk',           text: 'Facebook',      url: 'https://www.facebook.com/WINE-SHOP-Anyway-Grapes-500910809965433/' },
    { class: 'blogLnk',         text: 'Blog',          url: 'http://anyway-grapes.jp/blog' },
    { class: 'mailMagazineLnk', text: 'Mail Magazine', url: 'http://us8.campaign-archive2.com/home/?u=0ad89bde4937b5d71a1175566&id=136d202046' },
    { class: 'wholesaleLnk',    text: 'Wholesale',     url: '#' },
    { class: 'accessLnk',       text: 'Access',        url: '#' }
]

</header-block>

