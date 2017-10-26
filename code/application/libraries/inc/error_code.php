<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Err_Code {
    const ERR_OK =   '0000'; //成功
    
    //1001  --- 1099  系统错误
    const ERR_PARAM_SIGN =   '1001'; //签名认证不通过
    const ERR_DB =   '1002'; //服务器异常
    const ERR_PARA =   '1003'; //参数错误
    const ERR_FILE =   '1004'; //操作文件失败
    const ERR_DB_NO_DATA = "1005";//没有可操作的数据
    const ERR_TOKEN_SET_FAIL = '1006';// TOKEN设置失败
    const ERR_TOKEN_EXPIRE  = '1007';// TOKEN失效请重新登录
    const ERR_GET_TOKEN_FAIL = '1008';// TOKEN获取失败
    const ERR_LOGIN_BY_LOINGID_FAIL = '1009';// 百联三合一登录失败
    const ERR_MUST_DO_LOGIN_FAIL = '1010';// 请先登录,在执行操作
    const ERR_MC_SERVICE =   '1011'; //MC服务器异常
    
    const ERR_UNKOWNL =   '1099'; //未知错误
    
    
    // 2001 --- 2099    用户模块错误
    const ERR_INSERT_USER_INFO_FAIL = '2001';//插入用户信息失败
    const ERR_INSERT_USER_LOGINLOG_FAIL = '2002';//记录用户登录日志失败
    const ERR_UPDATE_USERINFO_FAIL = '2003';//用户信息修改失败
    const ERR_USER_LOGIN_FAIL = '2004'; // 用户登入失败
    const ERR_INSERT_USERLOGIN_FAIL = '2005'; // 插入注册信息失败
    const ERR_GET_USERINFO_FAIL = '2006'; // 获取用户信息失败
    const ERR_GET_REGISTER_INFO_FAIL = '2007';// 获取用户注册信息失败
    const ERR_USER_POINT_NOT_ENOUGH = '2008';// 账户积分不足

    const ERR_FEEDBACK_TOO_MUCH_CONTENT = '2009';// 用户反馈内容过长
    const ERR_CONTACT_TOO_LONG = '2010';// 用户联系方式过长
    const ERR_FEEDBACK_INSERT_FAIL = '2011';// 反馈信息插入失败
    
    const ERR_MESSAGE_DEL_FAIL = '2012';// 消息通知删除失败
    
    const ERR_SIGNIN_CONF_EMPTY = '2013';// 签到配置文件为空
    const ERR_SIGNIN_EXECUTE_FAIL = '2014';// 签到操作执行失败
    const ERR_SIGNIN_INSERT_HIS_FAIL = '2015';// 签到历史记录插入失败
    const ERR_DO_SIGNIN_EXISTS_FAIL = '2016';// 今天已经签过啦
    const ERR_NOT_ALLOW_APPEND_SIGN = '2017';// 该日期不允许补签
    const ERR_OUTNUMBER_APPEND_NUM = '2018';// 改月补签次数已满
    const ERR_SIGNIN_GET_POINT_FAIL = '2019';// 签到获得积分失败
    const ERR_APPEND_SIGNIN_REDUCE_POINT_FAIL = '2020';// 补签扣除积分失败
    const ERR_INSERT_POINT_CHANGE_HIS_FAIL = '2021';// 积分变更历史插入失败
    const ERR_SIGNIN_GAME_SCORE_RECORD_FAIL = '2022';// 签到游戏得分记录失败
    const ERR_SIGNIN_GAME_NOT_EXISTS = '2023';// 暂无签到游戏
    const ERR_UPDATE_BEST_SCORE_FAIL = '2024';// 签到游戏最好成绩更新失败
    const ERR_INSERT_BEST_SCORE_FAIL = '2025';// 签到游戏最好成绩插入失败
    
    const ERR_UPDATE_USER_INFO_FAIL = '2026';// 用户信息更新失败
    const ERR_BLCOIN_NOT_ENOUGHT_FAIL = '2027';// 游戏币不足
    const ERR_BLPOINT_NOT_ENOUGHT = '2028';// 百联积分不足
    const ERR_DATE_NOT_ALLOW_SIGNIN_ENOUGHT = '2029';// 该日期不允许签到

    // 2101 --- 2199    游戏模块错误
    const ERR_GAME_NOT_EXISTS_FAIL = '2101';// 游戏已下架
    const ERR_GAME_FAVORITE_FAIL   = '2102';// 游戏收藏失败
    const ERR_CANCEL_FAVORITE_FAIL = '2103';// 取消收藏失败
    const ERR_GAME_SHARE_FAIL = '2104';// 游戏分享失败
    const ERR_GAME_OPEN_FAIL = '2105';// 游戏打开失败
    const ERR_UPDATE_GAME_SHARE_NUM_FAIL = '2106';// 游戏分享次数更新失败
    const ERR_UPDATE_GAME_PLAY_PV_FAIL = '2107';// 游戏被打开次数更新失败
    const ERR_BUY_PROP_FAIL =  '2108';// 订单插入失败,购买道具失败
    const ERR_ONLINE_GAME_WITHOUT_CALLBACK = '2109';// 该网游暂无回调地址
    const ERR_ORDER_NOT_ALLOW_BUY   = '2110';// 该订单不允许支付
    
    const ERR_GAME_BOUGHT_FAIL   = '2111';// 游戏已购买
    const ERR_GAME_NOT_ALLOW_BUY_FAIL   = '2112';// 只允许购买金币游戏
    const ERR_BUY_HIS_INSERT_FAIL   = '2113';// 购买历史记录插入失败
    const ERR_BUY_BLCOIN_DEDUCT_FAIL  = '2114';// 购买游戏游戏币扣除失败
    const ERR_INSERT_BLCOIN_CHANGE_HIS_FAIL = '2115';// 游戏币变更历史插入失败

    // 2201 --- 2299    活动/专题错误
    
    
    
    // 2301 --- 2399    兑换错误
    const ERR_EXCHANGE_NOT_EXISTS_FAIL  = '2301';// 兑换券已下架
    const ERR_NOT_ALLOW_REPEAT_EXCHANGE_FAIL  = '2302';// 不允许重复兑换
    const ERR_INSERT_EXCHANGE_HIS_FAIL  = '2303';// 兑换历史记录插入失败
    const ERR_UPDATE_EXCHANGE_NUM_FAIL  = '2304';// 兑换次数更新失败
    const ERR_DEL_EXCHANGE_HIS_FAIL  = '2305';// 兑换记录删除失败
    const ERR_INSERT_EXCHANGE_MESS_FAIL  = '2306';// 消息快报插入失败
    const ERR_DEL_OLDEXCHANGE_MESS_FAIL  = '2307';// 旧消息快报删除失败
    const ERR_EXCHANGE_BLCOIN_DEDUCT_FAIL  = '2308';// 兑换时游戏币扣除失败
    const ERR_DO_THIRDPARTY_EXCHANGE_FAIL  = '2309';// 百联兑换接口调用失败

    const ERR_EXCHANGE_SERVICE_DOWN_FAIL  = '2310';// '维护,暂停服务',   //兑换操作错误码
    const ERR_EXCHANGE_PARA_FAIL  = '2311';// '兑换操作参数错误',
    const ERR_NOT_MOVENUMBER_FAIL  = '2312';// '非移动手机',
    const ERR_ONLY_MOVENUMBER_FAIL  = '2313';// '目前只支持移动直充',
    const ERR_MERCHANT_TOP_FAIL  = '2314';// '商户已被暂停',
    const ERR_EXCHANGE_SIGN_FAIL  = '2315';// '手机直充签名错误',
    const ERR_NOT_ALLOW_IP_FAIL  = '2316';// 'IP不合法',
    const ERR_MERCHANT_NOT_EXISTS_FAIL  = '2317';// '商户未开通',
    const ERR_EXCHANGE_REQUEST_ABNORMAL_FAIL  = '2318';// '请求异常',
    const ERR_DO_EXCHANGE_NOT_ALLOW_FAIL  = '2319';// '百联黑名单,请于隔日提交',
    const ERR_NOT_COUNTRYWIDE_NUMBER_FAIL  = '2320';// '非全国手机',
    const ERR_REMAINDER_NOT_ENOUGHT_FAIL  = '2321';// '余额不足',
    const ERR_ABOVE_QUOTA_FAIL  = '2322';// '超过移动限额',
    
    const ERR_FLOW_EXHCANGE_FAIL  = '2323';// '流量兑换失败',
    const ERR_MOBILE_EXHCANGE_FAIL  = '2324';// '话费兑换失败',
    const ERR_MOBILE_FOMAT_FAIL  = '2325';// 手机号格式不正确,
    const ERR_NOT_ALLOW_SH_MOBILE  = '2326';// 不允许充值上海手机号
    
    const ERR_NOT_ALLOW_EXCHANGE_FAIL  = '2327';// 不允许兑换
    const ERR_QUERY_EXCHANGE_INFO_FAIL  = '2328';// 兑换状态查询失败，请稍后兑换
    const ERR_EXCHANGE_BUILDING_FAIL  = '2329';// 兑换维护中，请稍后尝试
    const ERR_EXCHANGE_CLOSE_FAIL  = '2330';// 兑换商品关闭失败
    const ERR_NOT_ALLOW_EXCHANGE_SH_MOBILE = '2331';// 不予许充上海手机号
    
    
    // 2501 --- 2599    游戏币充值模块
    const ERR_PAY_BLCOIN_FAIL = '2501';// 游戏币充值失败
    const ERR_PAY_ORDER_INSERT_FAIL = '2502';// 充值订单插入失败
    const ERR_ORDER_CALLBACK_HANDLE_FAIL = '2503';//  订单异步通知处理失败
    const ERR_ORDER_NOT_ALLOW_PAY   = '2504';// 该订单不允许支付
    const ERR_GET_ORDER_INFO_EMPTY   = '2505';// 未查询到该订单
    const ERR_ORDER_NOT_ALLOW_CANCEL   = '2506';// 该订单不允许取消
    const ERR_ORDER_CANCEL_FAIL   = '2507';// 取消订单失败
    const ERR_ORDER_NOT_ALLOW_DEL   = '2508';// 该订单不允许删除
    const ERR_ORDER_DEL_FAIL   = '2509';// 删除订单失败
    const ERR_INSERT_RECHARGE_HIS_FAIL   = '2510';// 订单充值记录插入失败
    const ERR_POINTS_REDUCE_FAIL    = '2511';// 积分扣除失败
    const ERR_POINTS_RETURN_FAIL    = '2512';// 积分退款失败
    const ERR_UPDATE_ORDER_FAIL    = '2513';// 订单状态更新失败
    
    
    
    // 2601 --- 2699    百联认证授权 
    const ERR_GET_ACCESS_TOKEN_FAIL  = '2601';// access_token获取失败
    const ERR_GET_PUBLICKEY_FAIL  = '2602';// public_key获取失败
    const ERR_LOGIN_FOR_PASSPORTID_FAIL  = '2603';// passport_id登录失败
    const ERR_GET_BLPOINT_FAIL  = '2604';//百联积分查询失败
    const ERR_UPDATE_BLPOINT_FAIL  = '2605'; //百联积分更新失败
    
    
    // 2701 --- 2799  夺宝错误码
    const ERR_GET_CQSSC_INFO_FAIL  = '2701';// 重庆时时彩信息获取失败
    const ERR_NOT_EXISTS_DBGOODS_FAIL   = '2702';// 该夺宝商品已下架
    const ERR_NOT_ALLOW_TAKE_DUOBAO_FAIL = '2703'; // 该商品不允许夺宝  该商品不是处于"投注中..."状态
    const ERR_OVER_DUOBAO_ALLOW_BUY_NUM = '2704';// 超过允许购买人次数
    const ERR_DUOBAO_TAKE_ORDER_FAIL = '2705';// 夺宝下单插入失败
    const ERR_UPDATE_DB_ORDER_NUM_FAIL = '2706';// 夺宝下单人次数更新失败
    const ERR_GET_DBNO_MC_INFO_FAIL = '2707';// 夺宝编号获取失败
    const ERR_GET_SSCINFO_FAIL = '2708';// 时时彩信息获取失败
    const ERR_UPDATE_ADDRESS_FAIL = '2709';// 收货信息更新失败
    const ERR_ADD_ADDRESS_FAIL = '2710';// 收货信息新增失败
    const ERR_DEL_ADDRESS_FAIL = '2711';// 收货信息删除失败
    const ERR_NOT_FOUND_USER_DBORDER_FAIL = '2712';// 用户暂无该夺宝订单信息
    const ERR_UPDATE_USER_DBORDER_FAIL = '2713';// 夺宝订单更新失败
    const ERR_DBORDER_ADDRESS_EXISTS_FAIL = '2714';// 地址已绑定
    const ERR_DBORDER_FULLY_FAIL = '2715';// 该订单投满
    
    
}