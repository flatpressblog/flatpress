#### 日本語版追加情報 ####

日本の共用タイプのレンタルサーバ(さくらのレンタルサーバなど)の多くではグループ権限(パーミッション)を0にしないと実行を停止するよう設定されています。
テキストエディタでdefaults.phpを開き
```
26行目: define('FILE_PERMISSIONS', 0604);
27行目: define('DIR_PERMISSIONS', 0705);

30行目: define('CORE_FILE_PERMISSIONS', 0600);
31行目: define('CORE_DIR_PERMISSIONS', 0700);

34行目: define('RESTRICTED_FILE_PERMISSIONS', 0604);
35行目: define('RESTRICTED_DIR_PERMISSIONS', 0705);
```
にパーミッション値を直してからFTP転送してみましょう。

