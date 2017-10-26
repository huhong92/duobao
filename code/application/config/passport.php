<?php
// sign加密的key
$config['sign_key']     = 'DuoBaoSign7gXLvCu8h668o8buYRd73a';
$config['token_key']    = 'DuoBaoTokensfmepl7gXLvCu8hsoo732';
$config['token_expire'] = 30*24*3600;// TOKEN有效期30天
$config['blcoin_rate']  = 1;// 1人民币（分） = 1游戏币
$config['points_rate']  = 1;// 1人民币（分） = 1百联积分

// 图片资源地址
$config['source_url']    = "http://game.ibl.cn/";

// 夺宝配置信息
$config['access_token'] = 'DB_ATOKEN_';// access_token保存KEY
$config['duobao_no']    = 'DB_NO_';// 夺宝号存储位置  $prefix.$date_no.$goods_no
$config['sscluck_info'] = "DBSSC_INFO_";// 保存时时彩中奖信息

$config['open_duobao']  = '5';// 分钟; 夺宝开奖固定等待时间5分钟 + 每期时时彩开奖时间差


$config['dbinfo']   = array(
    'unjoin_info'   => '您还没有参加本期幸运夺宝哦',
    'join_info'     => '您参加本期幸运夺宝x人次，祝好运',
    'luck_info'     => '您参加本期幸运夺宝x人次，很幸运',
    'unluck_info'   => '您参加本期幸运夺宝x人次，很遗憾',
);
