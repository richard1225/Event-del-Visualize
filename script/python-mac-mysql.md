# 安装mysql的坑
使用的是dmg安装, 装完之后无法登陆, 然后卸载了使用brew安装, 成功进入

# 安装pma的坑
* mysqli_real_connect(): (HY000/2002): No such file or directory
> 修改config.inc.php中的localhost为127.0.0.1

* #2054 - The server requested authentication method unknown to the client
    * mysqli_real_connect(): The server requested authentication method unknown to the client [caching_sha2_password]
    * mysqli_real_connect(): (HY000/2054): The server requested authentication method unknown to the client
> 修改配置文件my.inf, 在mysqld下加多一行 default_authentication_plugin = mysql_native_password, 因为我的是mysql8, 密码验证方式是sha2, 而pma还是用老版本的验证方式, 这里 改成老版本的验证方式即可

# python操作mysql的坑
* 数据中的单双引号造成sql语句的错误, 需要转义一下, 单引号的转义是两个单引号'', 双引号的转义是两个双引号""

* 安装Mysql-Python报错,提示 没有my_config.h, 其实无需安装Mysql-Python, 只要装了 mysql-connector-python 就可以使用了
