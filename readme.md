# BaserCMS スケルトンプラグインBake Shell

### 概要

BaserCMSのプラグインのスケルトンコードをコマンドひとつで生成するshellです。
2014年11月1〜2日のBaser温泉合宿での成果物です。

### 動作検証したBaserCMSのバージョン

basercms-3.0.6 dev-3

### 設置方法

app/Console/Command/BcPluginShell.php

として設置して下さい。

### 使い方

$ php app/Console/cake.php BcPlugin プラグイン名

または

$ php app/Console/cake.php BcPlugin プラグイン名

通常のbake pluginと同様のメニューが表示されるので、同様に順次答えていくと指定ディレクトリにプラグインのスケルトンを生成します。

生成が完了したらBaserCMSの管理サイトのプラグイン管理からプラグインをインストールすることが出来ます。

### サンプルデータについて

合宿参加に当たって禁煙を誓った権藤さんにちなんで、テストデータに権藤さんのセリフを拝借致しました。禁煙頑張って下さい！

