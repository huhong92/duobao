<?php
/**
 * 一元夺宝操作
 * @author	huhong
 * @date	2016-12-16 15:10
 */
class Duobao_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('duobao_model');
    }
    
    /**
     * 获取夺宝商品列表
     */
    public function get_goods_list($params)
    {
        if ($params['type'] === 1) {
            $orderby    = "B.G_BOUGHTNUM DESC";
        } elseif($params['type'] === 2) {
            $orderby    = "B.ROWTIMEUPDATE DESC";
        } elseif($params['type'] === 3) {
            $orderby    = "A.G_BOUGHTNUM*100/(A.G_BLCOIN/A.G_SINGLEBLCOIN) DESC";
        }
        // 获取列表数据
        $select     = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,B.G_NAME name,B.G_ICON icon,B.G_TYPE type,A.G_BOUGHTNUM num,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN  price";
        $sql        = "SELECT ".$select." FROM bl_dbgoods A JOIN bl_dbgoods_conf B ON A.G_GOODSIDX = B.IDX AND G_BUYSTATUS = 1 AND A.STATUS = 0 AND B.STATUS = 0 ORDER BY ".$orderby;
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        if (!$g_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 过滤查询条件
        if ($params['cate_type']) {
            foreach ($g_list as $k=>&$v) {
                $type_arr   = explode(",", trim($v['type'],','));
                if (in_array($params['cate_type'], $type_arr)) {
                    $v['num']       = (int)$v['num'];
                    $v['total_num'] = $v['blcoin']/$v['price'];
                    unset($v['sign_blcoin']);
                    $new_list[] = $v;
                }
            }
        } else {
            foreach ($g_list as $k=>&$v) {
                $v['total_num'] = $v['blcoin']/$v['price'];
                unset($v['sign_blcoin']);unset($v['blcoin']);
                $new_list[] = $v;
            }
        }
        if (!$new_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $data['list']       = array_slice($new_list,$params['offset'],$params['pagesize']);
        $data['pagecount']  = ceil(count($new_list)/$params['pagesize']);
        return $data;
    }
    
    /**
     * 获取夺宝商品详细信息
     * @param array $params
     * @return array
     */
    public function get_goods_info($params)
    {
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,A.G_BOUGHTNUM num,B.G_LIMITBUY allow_num,B.G_NAME name,B.G_IMGS imgs,G_DETAIL detail,A.G_SINGLEBLCOIN  price";
        $condition      = "A.IDX = ".$params['id']." AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $g_info['imgs'] = explode(",", $g_info['imgs']);
        // 获取用户参与夺宝信息
        if ($params['uuid']) {
            $user_dbnum = $this->user_dbnum($params['uuid'],$g_info['id']);
            if ($user_dbnum) {
                $g_info['info'] = '您参加本期幸运夺宝'.$user_dbnum.'人次，祝好运';
            } else {
                $g_info['info'] = '您还没有参加本期幸运夺宝哦';
            }
        } else {
            $g_info['info'] = '您还没有参加本期幸运夺宝哦';
        }
        $g_info['is_duobao']   = (int)$user_dbnum?1:0;
        return $g_info;
    }
    
    /**
     * 查看当前用户可允许购买人次数上限
     * @param type $params
     */
    public function get_allow_dbnum($params)
    {
        
        $select = "A.IDX id,A.G_DATENO date_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BOUGHTNUM num,B.G_LIMITBUY limitbuy_num";
        $sql    = "SELECT ".$select." FROM bl_dbgoods A,bl_dbgoods_conf B WHERE A.IDX=".$params['id']." AND A.STATUS = 0 AND A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $g_info = $this->CI->duobao_model->exec_by_sql($sql);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取用户购买情况
        $user_dbnum = $this->user_dbnum($params['uuid'],$params['id']);
        $a_num      = $g_info['limitbuy_num'] - $user_dbnum;
        $b_num      = ($g_info['blcoin']/$g_info['single_blcoin']-$g_info['num']);
        $data   = array(
            'id'        => $g_info['id'],
            'date_no'   => $g_info['date_no'],
            'allow_num' => $a_num>$b_num?$b_num:$a_num,
            'num'       => $user_dbnum,
        );
        return $data;
    }

    /**
     * 获取用户参与夺宝次数
     * @param int $uuid  用户UUID
     * @param int $goods_id  夺宝商品表IDX
     */
    public function user_dbnum($uuid,$goods_id)
    {
        $sql    = "SELECT SUM(O_BUYNUM) num FROM bl_dborder WHERE O_USERIDX = ".$uuid." AND O_GOODSIDX = ".$goods_id;
        $result  = $this->CI->duobao_model->exec_by_sql($sql);
        return (int)$result['num'];
    }
    
    /**
     * 执行夺宝下单操作
     * @param type $params
     */
    public function do_take_order($params)
    {
        // 判断该商品是否允许下单、是否允许下注数
        $select         = "A.G_DATENO date_no,A.G_GOODSIDX goods_id,A.G_GOODSNO goods_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BOUGHTNUM bought_num,A.G_BUYSTATUS buy_status,A.G_GSTATUS g_status,B.G_LIMITBUY limitbuy_num";
        $condition      = "A.IDX = ".$params['id']." AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_NOT_EXISTS_DBGOODS_FAIL);
            return false;
        }
        if ($g_info['g_status'] != 1) { // 投注中
            $this->CI->error_->set_error(Err_Code::ERR_NOT_ALLOW_TAKE_DUOBAO_FAIL);
            return false;
        }
        $allowbuy_num   = ($g_info['blcoin']/$g_info['single_blcoin'])  - $g_info['bought_num'];
        if ($params['num'] > $g_info['limitbuy_num'] || $params['num'] > $allowbuy_num) {
            $this->CI->error_->set_error(Err_Code::ERR_OVER_DUOBAO_ALLOW_BUY_NUM);
            return false;
        }
        // 判断用户百联币是否足够
        $expend_blcoin  = $g_info['single_blcoin']*$params['num'];
        $u_info         = $this->CI->utility->get_user_info($params['uuid']);
        if ($u_info['blcoin'] < $expend_blcoin) {
            $this->CI->error_->set_error(Err_Code::ERR_BLCOIN_NOT_ENOUGHT_FAIL);
            return false;
        }
        // 随机生成 “中奖夺宝号”
        $dbno_arr   = $this->allo_dbno(array('date_no'=>$g_info['date_no'],'num'=>$params['num']));
        if (!$dbno_arr) {
            return false;
        }
        $dbno_info  = json_encode($dbno_arr);
        $this->CI->duobao_model->start();
        // 扣除用户百联币
        $table  = "bl_user";
        $fields = array("U_BLCOIN"=>$u_info['blcoin'] - $expend_blcoin);
        $where  = array('IDX'=>$params['uuid'],'STATUS'=>0);
        $upt_u  = $this->CI->duobao_model->update_data($fields,$where,$table);
        if (!$upt_u) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USERINFO_FAIL);
            return false;
        }
        
        // 插入夺宝订单表
        $dborder_no = $this->CI->utility->get_uniqid('DB');
        $table_1    = "bl_dborder";
        $data_1     = array(
            'O_NO'          => $dborder_no,
            'O_USERIDX'     => $params['uuid'],
            'O_NICKNAME'    => (string)$u_info['nickname'],
            'O_UIMAGE'      => $u_info['image'],
            'O_GOODSIDX'    => $g_info['goods_id'],
            'O_DATENO'      => $g_info['date_no'],
            'O_BUYNUM'      => $params['num'],
            'O_BLCOIN'      => $expend_blcoin,
            'O_IP'          => $this->ip,
            'O_RECORDNO'    => $dbno_info,
            'STATUS'        => 0,
        );
        $ist_o      = $this->CI->duobao_model->insert_data($data_1,$table_1);
        if (!$ist_o) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_DUOBAO_TAKE_ORDER_FAIL);
            return false;
        }
        
        // 更改夺宝期数表信息
        $table_2    = "bl_dbgoods";
        $fields_2   = array('G_BOUGHTNUM'=>$g_info['bought_num']+$params['num']);
        $where_2    = array('IDX'=>$params['id'],'STATUS'=>0);
        if ($allowbuy_num == $params['num']) {// 扫底购买
            $fields_2['G_BUYSTATUS']    = 2;// 1进行中[购买中] 2等待开奖3已揭晓
            $fields_2['G_GSTATUS']      = 2; // 1投注中..2等待开奖3已开奖等用户填写地址4等待发货5已发货
        }
        $upt_g      = $this->CI->duobao_model->update_data($fields_2,$where_2,$table_2);
        if (!$upt_g) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_DB_ORDER_NUM_FAIL);
            return false;
        }
        // 百联币变更历史记录
        $bl_data    = array(
            'G_USERIDX'     => $params['uuid'],
            'G_NICKNAME'    => $u_info['nickname'],
            'G_TYPE'        => 1,
            'G_SOURCE'      => 4,
            'G_BLCOIN'      => $expend_blcoin,
            'G_TOTALBLCOIN' => $u_info['blcoin'] -$expend_blcoin,
            'G_INFO'        => '夺宝消耗'.$expend_blcoin."游戏币",
            'STATUS'        => 0,
        );
        $ist_res    = $this->blcoin_change_his($bl_data);
        if (!$ist_res) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        // 清空MC夺宝号分配
        if ($fields_2['G_GSTATUS']  == 2) {
            $this->clean_allo_dbno($g_info['date_no']);
        }
        
        return $dbno_arr;
    }
    
    /**
     * 给用户分配夺宝号
     * @param type $params = array('uuid',)
     */
    public function allo_dbno($params)
    {
        $prefix = $this->CI->passport->get('duobao_no');
        $key    = $prefix.$params['date_no'];
        $info   = $this->CI->cache->memcached->get($key);
        if (!$info) {// 后期将编号信息存储一份到文件
            $this->CI->error_->set_error(Err_Code::ERR_GET_DBNO_MC_INFO_FAIL);
            return false;
        }
        // 分配夺宝号
        $dbno_info  = json_decode($info,true);
        $new_arr    = array_diff($dbno_info['total_dbno'], $dbno_info['allo_dbno']);
        if (!$new_arr) {
            $this->CI->error_->set_error(Err_Code::ERR_MC_SERVICE);
            return false;
        }
        $select_key = array_rand($new_arr,$params['num']);
        if ($params['num'] > 1) {
            // 重组编号
            foreach ($select_key as $v) {
                $arr[]                      = $new_arr[$v];
                $dbno_info['allo_dbno'][]   = $new_arr[$v];
            }
        } else {
            $arr[]                      = $new_arr[$select_key];
            $dbno_info['allo_dbno'][]   = $new_arr[$select_key];
        }
        // 将选中的号码，插入已分配列表中
        $save   = $this->CI->cache->memcached->save($key, json_encode($dbno_info),0);
        if (!$save) {
            $this->CI->error_->set_error(Err_Code::ERR_MC_SERVICE);
            return false;
        }
        return $arr;
    }
    
    /**
     * 清除夺宝号分配
     * @param type $params = array('uuid',)
     */
    public function clean_allo_dbno($date_no)
    {
        $prefix = $this->CI->passport->get('duobao_no');
        $key    = $prefix.$date_no;
        $info   = $this->CI->cache->memcached->get($key);
        if (!$info) {
            return true;
        }
        return $this->CI->cache->memcached->delete($key);
    }
    
    /**
     * 获取最新揭晓列表
     * @param type $params
     */
    public function get_publish_list($params) 
    {
        // 获取最新揭晓列表（1进行（只包含待开奖类型）2已揭晓 3全部）
        $condition      = "A.G_BUYSTATUS != 1 AND A.STATUS = 0 ORDER BY A.G_BUYSTATUS ASC,A.ROWTIMEUPDATE DESC";
        $join_condition = "A.G_GOODSIDX = B.IDX";
        $select         = "A.IDX id,A.G_GOODSNO goods_no,A.G_DATENO date_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_LUCKNO luckno,A.G_USERID luck_uuid,A.G_SSCDATE sscdate,A.D_SSCNO sscno,A.G_BUYSTATUS status,A.ROWTIMEUPDATE update_time,B.G_NAME name,B.G_ICON icon";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_list         = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b,true);
        if (!$g_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        
        // 获取下一期彩票开奖时间  + 开奖间隔时间
        $ssc_info   = $this->sscopen_info();
        if (!$ssc_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
            return false;
        }
        $open_time  = $this->CI->passport->get('open_duobao')*60;
        
        // 组合数据,判断数据是 已揭晓$status=1--揭晓中$status=2
        foreach ($g_list as $k=>$v) {
            if ($params['type'] == 1) {// 1进行（只包含准备开奖类型）2已揭晓 3全部
                if ($v['status'] == 3) {// 已揭晓
                    // 判断是否向前端展示中奖信息（揭晓时间是否 到达）
                    if ($v['sscdate'] == $ssc_info['expect']) {
                        if (time() >= $ssc_info['opentimestamp'] + $open_time) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                    $status =2;
                    $time   = $this->get_open_time($ssc_info);
                } else {
                    $status =2;
                    $time   = $this->get_open_time($ssc_info,1,strtotime($v['update_time']));
                }
            } elseif($params['type'] == 2) {
                if ($v['status'] == 2) {
                    continue;
                }
                if ($v['sscdate'] == $ssc_info['expect']) {
                    if (time() < $ssc_info['opentimestamp'] + $open_time) {
                        continue;
                    }
                }
                // 已揭晓商品，展示中奖用户
                $status = 1;
                $time   = strtotime($v['update_time']);
            } else {
                if ($v['status'] == 2) {
                    $status = 2;
                    $time   = $this->get_open_time($ssc_info,1,strtotime($v['update_time']));
                } else {
                    $status = 1;
                    $time   = strtotime($v['update_time']);
                     if ($v['sscdate'] == $ssc_info['expect']) {
                        if (time() < $ssc_info['opentimestamp'] + $open_time) {
                           $status = 2;
                           $time   = $this->get_open_time($ssc_info);
                        }
                    }
                }
            }
            
            if ($status == 1) {
                // 已揭晓商品，展示中奖用户
                $lucku_info = $this->CI->utility->get_user_info($v['luck_uuid']);
                $dbnum      = $this->user_dbnum($v['luck_uuid'], $v['id']);
                $v['luck_info']  = array(
                    'date_no'   => $v['date_no'],
                    'num'       => $dbnum,
                    'luck_user' => $lucku_info['nickname'],
                    'luck_uuid' => $v['luck_uuid'],
                    'time'      => strtotime($v['update_time']),
                    'luck_no'   => $v['luckno'],
                    'sscdate'   => $v['sscdate'],
                    'ccsno'     => $v['sscno'],
                );
                $v['time']   = strtotime($v['update_time']);
                unset($v['update_time']);
                $v['status'] = $status;
                $new_list[]  = $v;
            } else {
                $v['status'] = $status;
                $v['time']   = $time?$time:0;
                unset($v['update_time']);
                $new_list[]  = $v;
            }
            $v['total_num'] = $v['blcoin']/$v['single_blcoin'];
        }
        if (!$new_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        
        // 获取数据总页数
        $data['pagecount']  = ceil(count($new_list)/$params['pagesize']);
        $data['list']       = array_slice($new_list, $params['offset'],$params['pagesize']);
        return $data;
    }
    

    /**
     * 获取夺宝商品“中奖/待揭晓”等信息
     * @param type $params
     */
    public function get_publish_info($params)
    {
        // 1.获取夺宝商品信息
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,B.G_NAME name,B.G_IMGS imgs,G_DETAIL detail,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BUYSTATUS status,A.G_GSTATUS g_status,A.G_LUCKNO luckno,A.G_USERID luck_uuid,A.G_SSCDATE sscdate,A.D_SSCNO sscno,A.ROWTIMEUPDATE update_time";
        $condition      = "A.IDX = ".$params['id']." AND A.G_BUYSTATUS != 1 AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $g_info['imgs'] = explode(',', trim($g_info['imgs'],','));
        $luck_mess      = $this->CI->passport->get('dbinfo');
        if ($params['uuid']) {
            $user_dbnum     = $this->user_dbnum($params['uuid'], $g_info['id']);
            if ($user_dbnum) {
                $info = $luck_mess['join_info'];
            }
        } else {
            $info = $luck_mess['unjoin_info'];
        }
        
        // 获取下一期彩票开奖时间  + 开奖间隔时间
        $ssc_info   = $this->sscopen_info();
        if (!$ssc_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
            return false;
        }
        $open_time  = $this->CI->passport->get('open_duobao')*60;
        
        // 3.根据中奖倒计时 -- 判断是否展示中奖信息 status = 1已揭晓2待揭晓
        if ($g_info['status'] == 2) {// 等待开奖
            $status = 2;
            $time   = $this->get_open_time($ssc_info,1,strtotime($g_info['update_time']));
        } else {
             $status = 1;
             $time   = strtotime($g_info['update_time']);
            // 判断是否向前端展示中奖信息（揭晓时间是否 到达）
            if ($g_info['sscdate'] == $ssc_info['expect']) {
                if (time() < $ssc_info['opentimestamp'] + $open_time) {
                    // 显示待揭晓
                    $status = 2;
                    $time   = $this->get_open_time($ssc_info);var_dump($time);
                }
            }
        }
        // 4.地址信息：1:待揭晓2已开奖等用户填写地址3等待发货4已发货5已完成
        if ($g_info['g_status'] == 3) {
            $d_status  = 2;
        } else if($g_info['g_status'] == 4) {
            $d_status  = 3;
        } else if($g_info['g_status'] == 5) {
            $d_status  = 4;
        } else if($g_info['g_status'] == 6){
           $d_status  = 5;
        } else {
            $d_status  = 1;
        }
        
        if ($status == 1) {
            // 获取中奖用户信息
            $lucku_info = $this->CI->utility->get_user_info($g_info['luck_uuid']);
            $dbnum      = $this->user_dbnum($g_info['luck_uuid'], $g_info['id']);
            if ($g_info['luck_uuid'] == $params['uuid']) {
                $is_luck    = 1;
                $info = $luck_mess['luck_info'];
            } elseif($user_dbnum) {
                $info = $luck_mess['unluck_info'];
            }
            $luck_info  = array(
                'date_no'   => $g_info['date_no'],
                'num'       => $dbnum,
                'luck_icon' => $lucku_info['image'],
                'luck_user' => $lucku_info['nickname'],
                'luck_uuid' => $g_info['luck_uuid'],
                'time'      => $time<0?0:$time,
                'luck_no'   => $g_info['luckno'],
                'sscdate'   => $g_info['sscdate'],
                'sscno'     => $g_info['sscno'],
            );
        } else {
            $luck_info  = array(
                'sscdate'   => $g_info['sscdate'],
                'date_no'   => $g_info['date_no'],
                'time'      => $time<=0?0:$time,
            );
        }
        
        // 返回数据
        $data   = array(
            'id'        => $g_info['id'],
            'date_no'   => $g_info['date_no'],
            'name'      => $g_info['name'],
            'imgs'      => $g_info['imgs'],
            'total_num' => ($g_info['blcoin']/$g_info['single_blcoin']),
            'status'    => $status,
            'd_status'  => $d_status,
            'info'      => str_replace('x', (int)$user_dbnum, $info),
            'detail'    => $g_info['detail'],
            'luck_info' => $luck_info,
            'is_luck'   => $is_luck?1:0,
        );
        return $data;
    }
    
    /**
     * 我的夺宝记录列表
     * @param type $params
     */
    public function get_mypublish_list($params)
    {
        if ($params['type'] == 3) { // 1进行中(包含“正在参与的”、“准备开奖的”)2已揭晓 3我的中奖列表
            // 获取我的中奖列表
            $data   = $this->my_luck_list($params);
            return $data;
        }
        
        // 判断我是否参与夺宝、以及夺宝订单号
        $ids                = "";
        $options['where']   = array('O_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $options['fields']  = "O_GOODSIDX id";
        $options['groupby'] = "O_GOODSIDX";
        $order_his          = $this->CI->duobao_model->list_data($options,"bl_dborder");
        if (!$order_his) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        foreach ($order_his as $v) {
            $ids .=$v['id'].","; 
        }
        $ids    = trim($ids,",");
        
        // 获取我的夺宝列表
        $table  = "bl_dbgoods A,bl_dbgoods_conf B";
        $select = "A.IDX id,A.G_DATENO date_no,A.G_GSTATUS g_status,A.G_BUYSTATUS status,B.G_NAME name,B.G_ICON icon,A.G_USERID luck_uuid,A.G_SSCDATE sscdate,A.ROWTIMEUPDATE update_time,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN sigle_blcoin,A.G_LUCKNO luck_no";
        $sql    = "SELECT ".$select." FROM ".$table." WHERE A.G_GOODSIDX = B.IDX AND A.IDX IN (".$ids.") AND A.STATUS = 0 ORDER BY A.G_BUYSTATUS ASC,A.ROWTIMEUPDATE DESC";
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        
        // 获取下一期彩票开奖时间  + 开奖间隔时间
        $ssc_info   = $this->sscopen_info();
        if (!$ssc_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
            return false;
        }
        $open_time  = $this->CI->passport->get('open_duobao')*60;
        
        // 重组数据 $params['type']:1进行中(包含“正在参与的”、“准备开奖的”)2已揭晓 3全部
        foreach ($g_list as $k=>$v) {
            // 获取我参与的人次数、总人次数
            $v['num']       = $this->user_dbnum($params['uuid'], $v['id']);
            $v['total_num'] = ($v['blcoin']/$v['sigle_blcoin']);
            $status         = $v['status'];
            if ($params['type'] == 1) {
                if ($status == 3) {
                    if ($v['sscdate'] == $ssc_info['expect']) {
                        // 判断倒计时间--是否展示
                        if (time() < $ssc_info['opentimestamp'] + $open_time) {
                            $status = 2;
                            $time   = $this->get_open_time($ssc_info);
                        } else {
                            continue;
                        }
                    }else {
                        continue;
                    }
                } elseif($status == 2) {
                    $time   = $this->get_open_time($ssc_info,1,strtotime($v['update_time']));
                }
            } elseif($params['type'] == 2) {
                if ($status == 1 || $status == 2) {
                    continue;
                }
                if ($status == 3) {
                    if ($v['sscdate'] == $ssc_info['expect']) {
                        // 判断倒计时间--是否展示
                        if (time() < $ssc_info['opentimestamp'] + $open_time) {
                            continue;
                        }
                    }
                }
            } else {
                if ($status == 3) {
                    // 判断倒计时间--是否展示
                    if (time() < $ssc_info['opentimestamp'] + $open_time) {
                        $status = 2;
                        $time   = $this->get_open_time($ssc_info);
                    } else {
                        $time   = strtotime($v['ROWTIMEUPDATE']);
                    }
                } elseif($status == 2) {
                    $time   = $this->get_open_time($ssc_info,1,strtotime($v['update_time']));
                }
            }
            
            // 根据$status的状态，展示不同的内容
            if ($status == 3) {
                $v['is_luck']   = 0;
                if ($v['luck_uuid'] == $params['uuid']) {
                    $v['is_luck']   = 1;
                    if ($v['g_status'] == 3) {
                        $v['luck_status']   = 1;
                    } elseif($v['g_status'] == 4) {
                        $v['luck_status']   = 2;
                    }
                }
                // 获取中奖用户信息
                $luck_info      = $this->CI->utility->get_user_info($v['luck_uuid']);
                $v['luck_name'] = $luck_info['nickname'];
                $v['time']      = strtotime($v['update_time']);
                // 获取幸运用户参与次数
                $v['luck_num']  = $this->user_dbnum($v['luck_uuid'], $v['id']);
            } else {
                $v['time']      = $time?$time:0;
            }
            unset($v['g_status']);unset($v['update_time']);unset($v['b_status']);unset($v['blcoin']);unset($v['sigle_blcoin']);
            $v['status']  = $status;
            $new_list[]   = $v;
        }
        if (!$new_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        
        // 返回数据
        $data['pagecount']  = ceil(count($new_list)/$params['pagesize']);
        $data['list']       = array_slice($new_list, $params['offset'],$params['pagesize']);
        return $data;
    }
    
    /**
     * 用户获取夺宝中奖 订单信息
     * @param type $params
     */
    public function get_dborder_info($params)
    {
        $condition      = "A.IDX=".$params['id']." AND A.G_USERID = ".$params['uuid']." AND A.G_BUYSTATUS = 3 AND  A.STATUS = 0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,A.G_GSTATUS status,B.G_NAME name,B.G_ICON icon";
        $info           = $this->CI->duobao_model->left_join($condition, $join_condition,$select,"bl_dbgoods AS A","bl_dbgoods_conf AS B");
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        if ($info['status'] == 3) {
            $info['status'] = 1;
        } elseif($info['status'] == 4) {
            $info['status'] = 2;
        } elseif($info['status'] == 5) {
            $info['status'] = 3;
        }
        return $info;
    }
    
    /**
     * 获取夺宝商品参与历史记录
     * @param type $params
     */
    public function get_dbhistory($params)
    {
        // 获取数据总条数
        $table  = "bl_dborder";
        $where  = array('O_GOODSIDX'=>$params['id'],'STATUS'=>0);
        $total_count    = $this->CI->duobao_model->total_count($where,$table);
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取列表
        $options['where']   = $where;
        $options['fields']  = "IDX id,O_USERIDX uuid,O_NICKNAME name,O_UIMAGE image,O_BUYNUM num,O_IP ip,UNIX_TIMESTAMP(ROWTIME) time";
        $options['limit']   = array('size'=>$params['pagesize'],'page'=>$params['offset']);
        $options['order']   = "IDX DESC";
        $data['list']       = $this->CI->duobao_model->list_data($options,$table);
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        return $data;
    }
    
    /**
     * 获取夺宝消息快报
     * @param type $params
     */
    public function get_dbmessage($params)
    {
        $options['where']   = array('STATUS'=>0);
        $options['fields']  = "W_USERIDX uuid,W_NICKNAME name,W_GNAME gname,UNIX_TIMESTAMP(ROWTIME) time";
        $options['limit']   = array('size'=>100,'page'=>0);
        $options['order']   = "IDX DESC";
        $data['list']       = $this->CI->duobao_model->list_data($options,"bl_dbwin_his");
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $data;
    }
    
    /**
     * 获取商品分类列表
     * @param type $params
     * @return boolean
     */
    public function get_type_list($params)
    {
        $options['where']   = array('STATUS'=>0);
        $options['fields']  = "IDX id,G_TYPE type,G_NAME name";
        $data['list']       = $this->CI->duobao_model->list_data($options,"bl_dbgoods_type");
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $data;
    }
    
    /**
     * 商品期号订单绑定收货地址
     */
    public function do_bind_address($params)
    {
        // 查询订单状态
        $table  = "bl_dbwin_his";
        $where  = array('W_USERIDX'=>$params['uuid'],'W_GOODSIDX'=>$params['id'],'STATUS'=>0);
        $o_info = $this->CI->duobao_model->get_one($where,$table);
        if (!$o_info) {
            $this->CI->error_->set_error(Err_Code::ERR_NOT_FOUND_USER_DBORDER_FAIL);
            return false;
        }
        if ($o_info['W_RECEIVEIDX']) {
            $this->CI->error_->set_error(Err_Code::ERR_DBORDER_ADDRESS_EXISTS_FAIL);
            return false;
        }
        // 判断该收货地址
        $table_1= "bl_dbaddress";
        $where_1= array('IDX'=>$params['address_id'],'A_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $a_info = $this->CI->duobao_model->get_one($where_1,$table_1);
        if (!$a_info) {
            $this->CI->error_->set_error(Err_Code::ERR_ABOVE_QUOTA_FAIL);
            return false;
        }
        $this->CI->duobao_model->start();
        $fields = array('W_RECEIVEIDX'=>$params['address_id']);
        $upt_w  = $this->CI->duobao_model->update_data($fields,$where,$table);
        if (!$upt_w) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USER_DBORDER_FAIL);
            return false;
        }
        
        // 更新夺宝表，地址状态
        $fields2= array('G_GSTATUS'=>4);
        $where_2= array('IDX'=>$params['id'],'STATUS'=>0);
        $table2 = "bl_dbgoods";
        $upt_d2 = $this->CI->duobao_model->update_data($fields2,$where_2,$table2);
        if (!$upt_d2) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USER_DBORDER_FAIL);
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 获取地址列表
     */
    public function get_address_list($params)
    {
        // 获取总条数
        $table  = "bl_dbaddress";
        $where  = array('A_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $count  =$this->CI->duobao_model->total_count($where,$table);
        if (!$count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $options['where']   = $where;
        $options['fields']  = "IDX id,A_NAME receive,A_MOBILE mobile,A_ADDRESS address,A_USE is_use";
        $options['order']   = "A_USE DESC";
        $options['limit']   = array('size'=>$params['pagesize'],'page'=>$params['offset']);
        $data['list']       = $this->CI->duobao_model->list_data($options,$table);
        $data['pagecount']  = ceil($count/$params['pagesize']);
        return $data;
    }
    
    /**
     * 添加收货信息
     */
    public function do_add_address($params)
    {
        $this->CI->duobao_model->start();
        $fields = array('A_USE'=>0);
        $where  = array('A_USERIDX'=>$params['uuid'],'A_USE'=>1,'STATUS'=>0);
        $upt_a  = $this->update_address($fields,$where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $data   = array(
            'A_USERIDX' => $params['uuid'],
            'A_NAME'    => $params['receive'],
            'A_MOBILE'  => $params['mobile'],
            'A_ADDRESS' => $params['address'],
            'A_USE'     => 1,
            'STATUS'    => 0,
        );
        $ist_a  = $this->CI->duobao_model->insert_data($data,"bl_dbaddress");
        if (!$ist_a) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_ADD_ADDRESS_FAIL);
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 更新地址信息
     * @param type $params
     */
    public function do_update_address($params)
    {
        if ($params['receive']) {
            $fields['A_NAME']   = $params['receive'];
        }
        if ($params['mobile']) {
            $fields['A_MOBILE']   = $params['mobile'];
        }
        if ($params['address']) {
            $fields['A_ADDRESS']   = $params['address'];
        }
        if ($params['is_use'] == 1) {
            $fields['A_USE']    = 1;
        } elseif($params['is_use'] == 2) {
            $fields['A_USE']    = 0;
        }
        if (!$fields) {
            return true;
        }
        $this->CI->duobao_model->start();
        if ($fields['A_USE'] == 1) {
            $fields_1   = array('A_USE'=>0);
            $where_1    = array('A_USE'=>1,'A_USERIDX'=>$params['uuid']);
            $upt_a  = $this->update_address($fields_1, $where_1);
            if (!$upt_a) {
                $this->CI->duobao_model->error();
                return false;
            }
        }
        $where  = array('IDX'=>$params['id']);
        $upt_a  = $this->update_address($fields, $where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 更新地址信息
     * @param array $fields 更新数据
     * @param array $where  更新条件
     * @return bool
     */
    public function update_address($fields,$where)
    {
        $res = $this->CI->duobao_model->update_data($fields,$where,"bl_dbaddress");
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_ADDRESS_FAIL);
            return false;
        }
        return true;
    }
    
    /**
     * 删除地址
     */
    public function do_del_address($params)
    {
        $this->CI->duobao_model->start();
        $fields = array('STATUS'=>1);
        $where  = array('IDX'=>$params['id'],'A_USERIDX'=>$params['uuid']);
        $upt_a  = $this->update_address($fields, $where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 夺宝统一开奖方法
     */
    public function dbopen()
    {
        // 获取已夺宝完成列表(等待开奖的列表)
        $options['where']   = array('G_BUYSTATUS'=>2,'STATUS'=>0);
        $options['fields']  = "IDX id,G_DATENO date_no,G_GOODSIDX goods_idx,G_NAME name,G_BUYSTATUS b_status,G_GSTATUS g_status,G_BLCOIN blcoin,G_SINGLEBLCOIN single_blcoin,ROWTIMEUPDATE update_time";
        $prepare_list       = $this->CI->duobao_model->list_data($options,"bl_dbgoods");
        if (!$prepare_list) {
            return true;
        }
        // 获取最近一期ssc
        $ssc_info   = $this->sscopen_info();
        if (!$ssc_info) {
            log_message('error', "时时彩信息获取失败".time());
            return false;
        }
        $sscno  = implode("", explode(",", trim($ssc_info['opencode'],",")));
        // 判断该时时彩期号是否开过奖
        $open_time  = $this->CI->passport->get('open_duobao')*60;
        foreach ($prepare_list as $k=>$v) {
            if (strtotime($v['update_time']) >= $ssc_info['opentimestamp'] + $open_time) {
                continue;
            }
            $new_list[] = $v;
        }
        
        if (!$new_list) {
            return true;
        }
        $prepare_list   = $new_list;
        
        // 开奖操作
        $this->CI->duobao_model->start();
        foreach ($prepare_list as $k=>$v) {
            $luckno     = $this->luck_algorithm(array('sscno'=>$sscno,'total_num'=>($v['blcoin']/$v['single_blcoin'])));// 计算幸运号码
            $luck_user  = $this->get_luck_user($v['id'],$luckno);// 查找中奖用户
            // 更新夺宝期数表
            $data[]     = array(
                'IDX'           => $v['id'],
                'G_BUYSTATUS'   => 3,
                'G_USERID'      => $luck_user['uuid'],
                'G_SSCDATE'     => $ssc_info['expect'],
                'D_SSCNO'       => $sscno,
                'G_LUCKNO'      => $luckno,
                'G_GSTATUS'     => 3,
            );
            // 夺宝中彩历史记录表
            $data_2[]   = array(
                'W_USERIDX'     => $luck_user['uuid'],
                'W_NICKNAME'    => $luck_user['nickname'],
                'W_GOODSIDX'    => $v['id'],
                'W_GNAME'       => $v['name'],
                'W_DATENO'      => $v['date_no'],
                'W_BUYNUM'      => $luck_user['buy_num'],
                'W_LUCKNO'      => $luckno,
                'W_RECEIVEIDX'  => '',
                'W_LOGISTICSNO' => '',
                'W_LOGISTICSINFO'=> '',
                'W_STATUS'      => 0,
                'STATUS'        => 0,
            );
        }
        
        $upt_g  = $this->CI->duobao_model->update_batch($data,'IDX',"bl_dbgoods");
        if (!$upt_g) {
            log_message('error', "夺宝开奖数据更新失败");
            $this->CI->duobao_model->error();
            return false;
        }
        $ist_w  = $this->CI->duobao_model->insert_batch($data_2,'bl_dbwin_his');
        if (!$ist_w) {
            log_message('error', "夺宝开奖历史数据插入失败");
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 计算获取幸运号码
     * @param type $params
     */
    public function luck_algorithm($params)
    {
        // 幸运号码=（时时彩开奖号码÷奖品总需人次）取余数+10000001
        return ($params['sscno']%$params['total_num']) + 10000001;
    }
    
    /**
     * 获取用户中奖订单信息
     */
    public function get_luck_user($goods_id,$luckno)
    {
        $table              = "bl_dborder";
        $options['where']   = array('O_GOODSIDX'=>$goods_id,'STATUS'=>0);
        $options['fields']  = "O_USERIDX uuid,O_NICKNAME nickname,O_BUYNUM buy_num,O_RECORDNO dbno";
        $order_list         = $this->CI->duobao_model->list_data($options,$table);
        if (!$order_list) {
            return false;
        }
        foreach ($order_list as $k=>$v) {
            if (in_array($luckno, json_decode($v['dbno'],true))) {
                $array  = array('uuid'=>$v['uuid'],'nickname'=>$v['nickname'],'buy_num'=>$v['buy_num']);
                return $array;
            }
        }
        return false;
    }
    
    /**
     * 获取时时彩开奖信息，以及下期开奖时间
     */
    public function sscopen_info($row = 1)
    {
        $key    = $this->CI->passport->get('sscluck_info');
        $info   = $this->CI->cache->memcached->get($key);
        if ($info) {
            return json_decode($info,true);
        }
        // 上期开奖数据
        $url        = "http://f.apiplus.cn/cqssc-".$row.".json";
        $content    = $this->CI->utility->get($url);
        $content_arr= json_decode($content,true);
        if (!$content_arr) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_CQSSC_INFO_FAIL);
            return false;
        }
        $ssc_info   = $content_arr['data'][0];
        if (!$ssc_info) {
            return false;
        }
        
        // 获取下期开奖时间
        $time1  = strtotime(date('Ymd 00:00:00'));// 当天00点
        $time2  = strtotime(date('Ymd 01:55:00'));// 当天10点
        $time3  = strtotime(date('Ymd 10:00:00'));// 当天10点
        $time4  = strtotime(date('Ymd 22:00:00'));// 当天22点
        $time5  = strtotime(date('Ymd 23:59:59'));;// 当天23点59分59秒
        if ($ssc_info['opentimestamp'] >= $time1 && $ssc_info['opentimestamp'] < $time2) {
            // 每隔5分钟开奖
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp']+300;
        } elseif($ssc_info['opentimestamp'] >= $time2 && $ssc_info['opentimestamp'] < $time3) {
            // 间隔8小时5分钟
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp']+29100;
        } elseif($ssc_info['opentimestamp'] >= $time3 && $ssc_info['opentimestamp'] < $time4) {
            // 每隔10分钟开奖
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp']+600;
        } elseif($ssc_info['opentimestamp'] >= $time4 && $ssc_info['opentimestamp'] < $time5) {
            // 每隔5分钟开奖
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp']+300;
        }
        $this->CI->cache->memcached->save($key,json_encode($ssc_info),50);
        return $ssc_info;
    }
    
    
    
    // ----------------------------                   测试区            ----------------------
        
    /**
     * 获取时时彩中奖号码
     * @return int 5位的号码
     */
    public function ssc_luckno($row = 1)
    {
        $url        = "http://f.apiplus.cn/cqssc.json";
        $content    = $this->CI->utility->get($url);
        $content_arr= json_decode($content,true);
        if (!$content_arr) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_CQSSC_INFO_FAIL);
            return false;
        }
        return $content_arr;
    }
   // ----------------------------                   测试区            ----------------------
    /**
     * 获取用户获得夺宝号
     */
    public function get_duobao_nos($params)
    {
        $table              = "bl_dborder";
        $options['where']   = array('O_USERIDX'=>$params['uuid'],'O_GOODSIDX'=>$params['id']);
        $options['fields']  = "IDX id,O_NO no,O_RECORDNO duobao_nos";
        $list               = $this->CI->duobao_model->list_data($options,$table);
        $data   = array();
        if (!$list) {
            return $data;
        }
        foreach ($list as $k=>$v) {
            $data = array_merge(json_decode($v['duobao_nos']), $data);
        }
        return $data;
    }
    
    /**
     * 百联币变更历史记录
     */
    public function blcoin_change_his($data)
    {
        $table      = 'bl_blcoin_his';
        $ist_res    = $this->CI->duobao_model->insert_data($data,$table);
        if (!$ist_res) {
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_BLCOIN_CHANGE_HIS_FAIL);
            return false;
        }
        return true;
    }
    
    /**
     * 随机获取推荐商品
     */
    public function get_recomment_list($params)
    {
        // 获取该商品的商品库存信息
        $where  = array('IDX'=>$params['id'],'STATUS'=>0);
        $table  = "bl_dbgoods";
        $fields = "IDX id,G_GOODSIDX goods_id";
        $g_info = $this->CI->duobao_model->get_one($where,$table,$fields);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取该商品所属的类型
        $where2 = array('IDX'=>$g_info['goods_id'],'STATUS'=>0);
        $table2 = "bl_dbgoods_conf";
        $fields2= "G_TYPE type";
        $c_info = $this->CI->duobao_model->get_one($where2,$table2,$fields2);
        if (!$c_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $type_arr   = explode(",", trim($c_info['type'],","));
        if (empty($type_arr)) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $key    = array_rand($type_arr,1);
        $type   = $type_arr[$key];
        
        // 获取正在夺宝的商品
        $select     = "A.IDX id,A.G_DATENO date_no,B.G_ICON icon,B.G_TYPE type,A.G_BOUGHTNUM num,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN  price";
        $sql        = "SELECT ".$select." FROM bl_dbgoods A JOIN bl_dbgoods_conf B ON A.G_GOODSIDX = B.IDX AND G_BUYSTATUS = 1 AND A.STATUS = 0 AND B.STATUS = 0";
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        if (!$g_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 过滤跟当前商品不是同一类型的商品
        foreach ($g_list as $k=>&$v) {
            $type_  = explode(",", trim($v['type'],','));
            if (in_array($type, $type_) && $v['id'] != $g_info['id']) {
                $temp['id']     = $v['id'];
                $temp['icon']   = $v['icon'];
                $temp['num']    = $v['blcoin']/$v['price'] - (int)$v['num'];
                $new_list[]     = $temp;
            }
        }
        if (!$new_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 随机获取3条
        if (count($new_list) <= 3) {
            return $new_list;
        }
        $key_arr = array_rand($new_list,3);
        $data[] = $new_list[$key_arr[0]];
        $data[] = $new_list[$key_arr[1]];
        $data[] = $new_list[$key_arr[2]];
        return $data;
    }
    
    /**
     * 获取我的中奖列表
     */
    public function my_luck_list($params)
    {
        // 获取总条数
        $sql    = "SELECT COUNT(IDX) num FROM bl_dbgoods WHERE G_USERID = ".$params['uuid']." AND STATUS = 0";
        $total  = $this->CI->duobao_model->exec_by_sql($sql);
        if (!$total['num']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取我的中奖列表
        $table  = "bl_dbgoods A,bl_dbgoods_conf B";
        $select = "A.IDX id,A.G_DATENO date_no,A.G_BUYSTATUS status,B.G_NAME name,B.G_ICON icon,A.G_USERID luck_uuid,A.ROWTIMEUPDATE update_time,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN sigle_blcoin,A.G_LUCKNO luck_no";
        $sql    = "SELECT ".$select." FROM ".$table." WHERE  A.G_USERID = ".$params['uuid']." AND  A.G_GOODSIDX = B.IDX AND A.STATUS = 0 ORDER BY A.G_BUYSTATUS ASC,A.ROWTIMEUPDATE DESC LIMIT ".$params['offset'].",".$params['pagesize'];
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        // 重组数据
        foreach ($g_list as $k=>&$v) {
            // 获取我参与的人次数
            $v['num']   = $this->user_dbnum($params['uuid'], $v['id']);
            // 总人次数
            $v['total_num'] = ($v['blcoin']/$v['sigle_blcoin']);
            $v['time']      = strtotime($v['update_time']);
            unset($v['update_time']);unset($v['blcoin']);unset($v['sigle_blcoin']);
        }
        
        $data['pagecount']  = ceil($total['num']/$params['pagesize']);
        $data['list']       = $g_list;
        return $data;
    }

    /**
     * 获取开奖倒计时
     * @param type $type 1:夺宝系统处于揭晓中状态2已揭晓状态
     * @return int 时间S
     */
    public function get_open_time($ssc_info,$type = 2,$upt_time = 0)
    {
        $open_time  = $this->CI->passport->get('open_duobao')*60;
        if ($type == 1) {// 夺宝系统中，时时彩未开奖情况
            if ($ssc_info['opentimestamp'] > $upt_time) {
                $time   = $ssc_info['opentimestamp'] + $open_time - time();
            } else {
                $time   = $ssc_info['nextopen_time'] + $open_time - time();
            }
        } else {// 开奖揭晓时间差
            $time   = $ssc_info['opentimestamp'] + $open_time - time(); 
        }
        return $time;
    }
    
    /**
     * 查询夺宝订单物流信息
     * @param type $params
     */
    public function do_query_logistics($params)
    {
        $tabel  = "bl_dbwin_his";
        $where  = array('W_GOODSIDX'=>$params['id'],'W_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $fields = "W_STATUS status,W_LOGISTICSNO logistics_no,W_LOGISTICSINFO info";
        $data   = $this->CI->duobao_model->get_one($where,$tabel,$fields);
        if (!$data) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $data;
    }
    
}

