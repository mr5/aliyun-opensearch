# aliyun-opensearch

[![Travis-ci](https://scrutinizer-ci.com/g/mr5/aliyun-opensearch/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mr5/aliyun-opensearch/)
[![Build Status](https://travis-ci.org/mr5/aliyun-opensearch.svg)](https://travis-ci.org/mr5/aliyun-opensearch)
[![Coveralls](https://coveralls.io/repos/mr5/aliyun-opensearch/badge.svg?branch=master&service=github)](https://coveralls.io/github/mr5/aliyun-opensearch?branch=master)
![Wordpress 4.2](https://img.shields.io/badge/wordpress-4.4.x-blue.svg)
![Wordpress 4.2](https://img.shields.io/badge/wordpress-4.3.x-blue.svg)
![Wordpress 4.2](https://img.shields.io/badge/wordpress-4.2.x-blue.svg)
![Wordpress 4.1](https://img.shields.io/badge/wordpress-4.1.x-blue.svg)
![Wordpress 4.0](https://img.shields.io/badge/wordpress-4.0.x-blue.svg)
![Wordpress 3.9](https://img.shields.io/badge/wordpress-3.9.x-blue.svg)
![Wordpress 3.8](https://img.shields.io/badge/wordpress-3.8.x-blue.svg)
![PHP >= 5.3](https://img.shields.io/badge/php-%3E=5.3-green.svg)

## Introduction
Aliyun Open Search is a hosting service for structured data searching. Supporting data structures, sorting and data processing freedom to customize. Aliyun Open Search provides a simple, low cost, stable and efficient search solution for your sites or applications.

This plugin make an integration of WordPress and AliYun Open Search in an easy way.

## Installation

Download the [latest release](https://github.com/mr5/aliyun-opensearch/releases) of this plugin (the file ends with `.zip`).

Open your WordPress admin dashboard: 

1. Open the `plugins` page;
2. Click the `Add New` button;
3. Click the `Upload Plugin` button;
4. Select the zip file you downloaded previous, and click the `Install Now` button;
5. Activate plugin (Just click the link named `activate` after installed success)

## Configuration
1. Open your WordPress admin dashboard.
1. Find the menu `Plugins` - `Installed Plugins`, click it, then make sure the plugin named `AliYun Open Search` has been activated (Just click the `Activate` link).
1. You can open the options page with `Settings` - `阿里云搜索` link on left menu. 
1. Find the link named `下载模板`,  click it, you will download an file named `index-template.json`.
1. Open AliYun Open Search dashboard with link named `阿里云面板`,  then click the link named `模板管理` to create a template for your wordpress search application.
	1. Click the button named `创建模板`;
	1. Type in a `模板名称` such as `wordpress_plugin` , then click the button named `下一步`;
	1. Click the button named `导入模板`, then upload the file named `index-template.json` that you downloaded in previous steps. 
	2. Click the `下一步` button;
	3. Click the `创建` button on the bottom of page.
	4. It means success after you saw the messages like`创建模板成功`.
	5. Click the link named `返回模板列表`.
1. Click the link named `应用列表` on left menu to create an application.
	1. Click the `创建应用` button;
	2. Make a name for the `应用名称` field, and remember it, it will be useful in next steps.
	3. Select a region, the same region is recommended when your WordPress deployed on AliYun ECS or ACE. It will make the API communication faster.
	4. Click the `下一步` button;
	5. Select the template that you created previous (such as `wordpress_plugin`).
	6. Click the `下一步` button on the bottom of page.
	7. Click the `稍后手动上传` button, then copy the value of `公网API域名` , `内网API域名` is recommended when your WordPress deployed with the same region of  search application.
1. Go back to your wordpress dashboard. And fill the options form.
2. After the options saved with the `保存更改` button,  click the `重建索引` button if you have written some posts or pages before to index them.
1. DONE!

## See also
http://www.aliyun.com/product/opensearch/
