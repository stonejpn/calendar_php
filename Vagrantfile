# -*- mode: ruby -*-
# vi: set ft=ruby :
HOST_NAME = "calendar-php.local"

MEMORY = 256
CPU = 1

$init_script = <<-SCRIPT
hostnamectl set-hostname #{HOST_NAME}
timedatectl set-timezone Asia/Tokyo
# kernel-develのインストール時にエラーになるため、キーを個別にimport
rpm -import https://repo.almalinux.org/almalinux/RPM-GPG-KEY-AlmaLinux
dnf install -y kernel-devel man man-pages vim tmux epel-release
dnf update -y
SCRIPT

$setup_script = <<-SCRIPT
dnf install -y nginx
SCRIPT

Vagrant.configure("2") do |config|
  config.vm.box = "bento/almalinux-9"
  config.vm.network "forwarded_port", guest: 80, host: 8080

  config.vm.provider "virtualbox" do |vb|
    vb.name = HOST_NAME
    vb.memory = MEMORY
    vb.cpus = CPU

    #
    # specifed in ~/vagrant.d/Vagrantfile
    #
    # prl.linked_clone = true
    # prl.update_guest_tools = true
    # prl.customize ["set", :id, "--startup-view", "window"]
  end

  config.vm.provision "init", type: "shell", inline: $init_script
  config.vm.provision "tool", run: "never", type: "shell", inline: "/usr/bin/ptiagent-cmd --install"
  config.vm.provision "setup", run: "never", type: "shell", inline: $setup_script
end
