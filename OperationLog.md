# カレンダー実装メモ



普段、こういったログを取りながら、作業を行っています。



## サーバーのセットアップ

最終的にDockerにするけど、ひとまず、ごちゃごちゃ考えずに、Vagrantでサーバーを立てる。



### 立ち上げ

kernel-develのinstallでコケる

```
$ sudo rpm -import https://repo.almalinux.org/almalinux/RPM-GPG-KEY-AlmaLinux
```

の実行後に、インストール成功。

→ Vagrantfileへ反映



```
$ vagrant halt
$ vagrant destroy
$ vagrant up
```

で、正常に起動できることを確認



その後のリブートで、

```
default: /vagrant => /Users/stone/Documents/workspace/calendar/PHP
Vagrant was unable to mount Parallels Desktop shared folders. This is usually
because the filesystem "prl_fs" is not available. This filesystem is
made available via the Parallels Tools and kernel module.
Please verify that these guest tools are properly installed in the
guest. This is not a bug in Vagrant and is usually caused by a faulty
Vagrant box. For context, the command attempted was:

mount -t prl_fs -o uid=1000,gid=1000,_netdev vagrant /vagrant

The error output from the command was:

mount: /vagrant: unknown filesystem type 'prl_fs'.
```

とエラーが発生。



boxをアップデートして試してみる。

```
# 既存のインスタンスを削除
$ vagrant down
$ vagrant destroy

$ vagrant box update --box "bento/almalinux-8"

$ vagrant up
```

別なエラーが発生

```
==> default: Booting VM...
There was an error while command execution. The command and stderr is shown below.

Command: ["/usr/local/bin/prlctl", "start", "dd90d41e-6f78-471c-8148-e15923ae898f"]

Stderr: Failed to start the VM: To start "calendar-php.local", allow the Parallels Hypervisor System Extension and restart your Mac. Open the macOS System Preferences > Security & Privacy > General and click Allow. Or if you don’t want to change the macOS preferences, there’s another way – switch the hypervisor type to “Apple” in the virtual machine configuration. For more details, please see here (https://www.parallels.com/products/desktop/supportinfo/pdfm19_C_56521).
```

ParallelsDesktopをアップデートしたあとの操作が足りてなかった。

言われた通り、セキュリティ＆プライバシーを変更して、再起動。



```
$ vagrant up
$ vagrant reload --provision-with tool
```





### 実行環境を整備

nginx + PHP-FPM + PHPで構築

```
vhost # dnf install nginx
vhost # dnf install php
```

phpインストール時に`php-fpm`も合わせてインストールされる。



Vagrantでポートフォワーディング

```
@@ -16,6 +16,7 @@ SCRIPT
 
 Vagrant.configure("2") do |config|
   config.vm.box = "bento/almalinux-8"
+  config.vm.network "forwarded_port", guest: 80, host: 8080
 
   config.vm.provider "parallels" do |prl|
     prl.name = HOST_NAME
```

http://localhost:8080 で接続を確認

calendarをルートディレクトリに作成

```
vhost $ sudo ln -s /vagrant/htdocs calendar
```



ダミーのindex.phpを用意して表示

```php
<?php
phpinfo();
```



### PHPのバージョンを指定してインストール

なんかおかしいと思ったら、PHPのバージョンが7.2.xだった。

ゲストを作り直す。



## 開発環境をWSL2に移行

仮想環境はParallels DesktopからVirutalBox(Windows版)へ移行。

WSL2のLinuxにインストールされたVirutalBoxは、vagrantで操作できないため、vagrant(Linux上)→VitualBox(Windows上)という構成。



```
Vagrant is currently configured to create VirtualBox synced folders with
the `SharedFoldersEnableSymlinksCreate` option enabled. If the Vagrant
guest is not trusted, you may want to disable this option. For more
information on this option, please refer to the VirtualBox manual:

  https://www.virtualbox.org/manual/ch04.html#sharedfolders

This option can be disabled globally with an environment variable:

  VAGRANT_DISABLE_VBOXSYMLINKCREATE=1

or on a per folder basis within the Vagrantfile:

  config.vm.synced_folder '/host/path', '/guest/path', SharedFoldersEnableSymlinksCreate: false
```



### 実行環境はDockerへ移行

当初から、最終形はDockerにする予定だったし、WSL2 + VirtualBoxですんなり起動しないので、このタイミングで実行環境をDockerに移行する。



https://hub.docker.com/_/php

```
$ docker run -d -p 80:8001 --name apache-php -v "$PWD/htdocs:/var/www/html" php:8.3-apache
```

繋がらない。



```
$ docker run --rm -p 8000:80 --name apache-php -v "$PWD/htdocs:/var/www/html" php:8.3-apache
```

`-p`オプションは、ホスト:コンテナの順番だった



bashを起動

```
$ docker exec -it <コンテナID> /bin/bash
```



/etc/apache2/sites-available/000-default.conf

```
<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        #ServerName www.example.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html

        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        # For most configuration files from conf-available/, which are
        # enabled or disabled at a global level, it is possible to
        # include a line for only one particular virtual host. For example the
        # following line enables the CGI configuration for this host only
        # after it has been globally disabled with "a2disconf".
        #Include conf-available/serve-cgi-bin.conf
</VirtualHost>
```

ドキュメントルートはハードコーディングされてるので、000-default.confは書き換える必要がある。



docker/ディレクトリとDockerfileを用意

```
$ cd docker
$ docker build -t php-app .
```



https://docs.docker.jp/engine/reference/commandline/build.html

> 多くの場合、それぞれの Dockerfile を空のディレクトに入れるのがベストな方法です。それから、ディレクトリ内には Dockerfile の構築に必要なものしか置きません。構築のパフォーマンスを向上するには、 `.dockerignore` ファイルを設置し、特定のファイルやディレクトリを除外する設定が使えます。



