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

