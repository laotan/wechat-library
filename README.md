wechat-library
==============

微信简易图书馆
--------------

具体的实现思路详见http://laotan.net/2013/09/wechat-php-library/

使用了@wechat-php-sdk作为消息基础类

整个应用部署在SAE，数据库操作使用的是官方的db类，如果要部署到自己的服务器，需要修改db类

### 配置说明

配置微信公众平台的自定义`token`
	
	define("TOKEN","yourtoken");

配置数据库报错转义，不定义将直接返回数据库error

	define("ERR_MSG","请求出错，臣妾做不到啊~");

DEBUG模式，可以将php错误发送到微信客户端

	new MyWechat(TOKEN, FALSE);//TRUE开启

