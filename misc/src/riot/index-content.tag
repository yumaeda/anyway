<index-content>

<div class="contents">
    <div class="mainContents">
        <virtual each="{ character in characters }">
            <br><br><br>
            <h2 if="{ character.title }">{ character.title }</h2>
            <p>{ character.description }</p>
        </virtual>
        <div class="ad_block" if="{ tastingEvent.length > 0 }">
            <a href="{ tastingEvent.pageUrl }"><img src="{ tastingEvent.bannerUrl }" alt="{ tastingEvent.title }"></a>
            <br><br>
            <a id="tasting-link" href="#">&gt;&gt;&nbsp;過去の試飲イベント</a>
        </div>
    </div>
</div>

this.characters =
[
    {
        title:       '',
        description: '地下１階に店舗を構えている当店では、厳格な温度管理と湿度管理を行っており、取り扱っているワインの品質には万全を期しています。現地から日本国内までリーファーコンテナで運んでいるワインは現地のままの味を、その他のワインもインポーターの出荷時と同じクオリティを保っております。'
    },

    {
        title:       'OUR WINE',
        description: 'ブランド、流行にはこだわらず、人の手によって丁寧に扱われた葡萄からできたワイン、小規模生産者の思いの詰まったワインを多数取り揃えました。 商品は1000アイテム以上。毎日を楽しくする1000円台のデイリーワインから特別な日を華やかに演出するグラン・ヴァンやシャンパーニュ、入手困難なバックビンテージ、生産量の少ない希少アイテムなど幅広い品揃えでお客様に一人一人のお好みや目的に合わせた１本をお探し致します。ワインを保管するセラーだけでなく、ワインの売り場も徹底した温度管理と湿度管理をしています。'
    },

    {
        titile:       'CONCIERGE',
        description: '20年間飲食事業に携わった経験を生かし、ご予算や好みのタイプに合わせて日々の家庭料理と相性のいいワインをご提案させて頂きます。'
    },

    {
        title:       'NATURAL FOOD',
        description: '時間と手間をかけて造られたオーガニックヴィネガー、オリーブオイル、ルイボスティー、マスタード、はちみつ、ドライフルーツ、ナチュラル・ジュースなど、日々の食事を大切にしたい人におすすめのナチュラル・フードをご用意しております。ちょっとしたギフトにも最適です。'
    },

    {
        title:       'TASTING EVENT',
        description: '姉妹店Mitsu-Getsuなどのレストランとコラボしたワイン会や生産者の話が直接聞けるメーカーズ・ディナー、季節やテーマに合わせたお買い得なワインの試飲販売会などを定期的に開催致します。イベントの詳細はブログやフェイスブックにてご案内しております。'
    }
]

this.tastingEvent =
{
    title:     '2017.02.05(日)ジュラ・ワイン試飲販売会',
    pageUrl:   'https://goo.gl/Py1dhR',
    bannerUrl: 'http://anyway-grapes.jp/images/jura_tasting.png'
}

</index-content>

