# PayWarp

お金を払ってワープができる<br>
新しいワープシステムです<br>
<hr>
基本コマンド<br>
/pw help  ヘルプ<br>
/pw warp  ワープに使う<br>
/pw list  ワープ先を見る<br>
<hr>
OP用コマンド<br>
/pw add <ワープ地点名> ワープ先を追加<br>
/pw del <ワープ地点名>  ワープ先を削除<br>
/pw payset <値段> ワープに使う値段を変える  もしかしたら /reload が必要かもしれません<br>
<hr>
pw.ymlの内容
<pre>Pay: 300    ここを変えればワープに使う値段を変えれます ゲーム内の/pw paysetと同じ 変えた後は /reload をしてください
Plugin: EconomyAPI    ここは鯖が使ってるお金のプラグインですここを変えることで別のお金プラグインを使うことができます
                      現在対応してるプラグイン
                      EconomyAPI
                      MoneySystem
                      LevelMoneySystem
                      MoneyPlugin
                      MixCoinSystem
                      もしかしたら対応できてないものもあるかもしれません
                      対応していなかったら
                      MCBEForumの議論に書いてください</pre>

License : GNU GPLv3
