# Dockerize 🐳 plugin for CakePHP 🍰
The goal of this project is to make docker project configuration, using cakephp3, easy.
Before using this plugin you have to setup basic docker configuration using
my config [cakephp3-docker](https://github.com/RoBYCoNTe/cakephp3-docker.git).

## Installation
You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require robyconte/Dockerize
```

After that you can setup your application to get it ready to use 
[cakephp3-docker](https://github.com/RoBYCoNTe/cakephp3-docker.git) running
this command from the command line:
```
bin/cake Dockerize.setup exec
´´´
