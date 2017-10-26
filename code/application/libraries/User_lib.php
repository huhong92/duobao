<?php
/**
 * 用户操作
 * @author	huhong
 * @date	2016-08-24 16:11
 */
class User_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('user_model');
    }
    
    /**
     * 登录操作
     * @param type $params
    */
    public function do_login($params)
    {
        $this->CI->user_model->start();
        //查询该用户是否已注册
        $_uuid = $this->chk_user_account($params['user_id'],$params['channel']);
        if ($_uuid === false) {
            //新注册用户 插入用户表
            $data   = array(
                'U_NAME'            => $params['nickname'],
                'U_ICON'            => (string)$params['image'],
                'U_SEX'             => (string)$params['sex'],
                'U_BLCOIN'          => 0,
                'U_MOBILEPHONE'     => (string)$params['mobile'],
                'U_LASTLOGINTIME'   => $this->zeit,
                'U_CHANNEL'         => $params['channel'],
                'STATUS'            => 0,
            );
            $_uuid = $this->CI->user_model->insert_data($data,'bl_user');
            if (!$_uuid) {
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_USER_INFO_FAIL);
                return false;
            }
            //插入用户登入表
            $data2  = array(
                'U_USERIDX'     => $_uuid,
                'U_ACCOUNTID'   => $params['user_id'],
                'U_ACCOUNTNAME' => $params['nickname'],
                'U_CHANNEL'     => $params['channel'],
                'STATUS'        => 0,
            );
            $rst = $this->CI->user_model->insert_data($data2,'bl_userlogin');
            if (!$rst) {
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_USERLOGIN_FAIL);
                return false;
            }
        } else {
            // 更新用户信息
            $where  = array('IDX'=>$_uuid,'status'=>0);
            $fields = array(
                'U_NAME'            => $params['nickname'],
                'U_ICON'            => (string)$params['image'],
                'U_SEX'             => (string)$params['sex'],
                'U_MOBILEPHONE'     => (string)$params['mobile'],
                'U_LASTLOGINTIME'   => $this->zeit,
                'U_CHANNEL'         => $params['channel'],
            );
            $rst = $this->CI->user_model->update_data($fields,$where,'bl_user');
            if (!$rst) {
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USERINFO_FAIL);
                return false;
            }
        }
        
        //记录用户登录历史记录
        $data3  = array(
            'L_USERIDX'     => $_uuid,
            'L_NAME'        => $params['nickname'],
            'L_CHANNEL'     => $params['channel'],
            'L_IP'          => $this->ip,
            'STATUS'        => 0,
        );
        $rst = $this->CI->user_model->insert_data($data3,'bl_loginlog');
        if (!$rst) {
            $this->CI->user_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_USER_LOGINLOG_FAIL);
            return false;
        }
        $this->CI->user_model->success();
        //获取用户信息
        $data   = $this->get_userinfo(array('uuid'=>$_uuid));
        if (!$data) {
            return false;
        }
        $data['token']  = $this->CI->gen_login_token($_uuid, $params['channel']);
        if (!$data['token']) {
            return false;
        }
        return $data;
    }
    
    
    
    /**
     * 校验用户是否已注册
     */
    public function chk_user_account($user_id,$channel)
    {
        $where          = array('U_ACCOUNTID'=>$user_id,'U_CHANNEL'=>$channel,'status'=>0);
        $fields         = "U_USERIDX AS uuid";
        $register_info  = $this->CI->user_model->get_one($where, 'bl_userlogin', $fields);
        if ($register_info['uuid']) {
            return $register_info['uuid'];
        }
        return false;
    }
    
    /**
     * 获取用户注册信息
     */
    public function get_register_info($uuid)
    {
        $condition      = "A.IDX = ".$uuid." AND A.STATUS = 0 AND B.STATUS = 0";
        $join_condition = "A.IDX = B.U_USERIDX";
        $select         = "A.IDX uuid,A.U_NAME name,A.U_BLCOIN blcoin,A.U_MOBILEPHONE mobile,A.U_SN sn,A.U_CHANNEL channel,A.U_PASSPORTID passport_id,B.U_ACCOUNTID user_id";
        $info   = $this->CI->user_model->left_join($condition, $join_condition,$select,'bl_user A','bl_userlogin B');
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $info;
    }
    
    /**
     * 注销用户
     * @param type $params
     * @return boolean
     */
    public function do_logout($params)
    {
        $token_key  = $this->CI->passport->get('token_key');
        $key        = $params['uuid']."_".$params['channel']."_".$token_key;
        $token_info = $this->CI->cache->memcached->get($key);
        if (!$token_info) {
            return true;
        }
        $this->CI->cache->memcached->delete($key);
        return true;
    }
    
    /**
     * 获取用户基本信息
     */
    public function get_userinfo($params)
    {
        $where  = array('IDX'=>$params['uuid'],'status'=>0);
        $fields = "IDX AS uuid,U_NAME AS nickname ,U_ICON image,U_SEX sex,U_BLCOIN blcoin,U_MOBILEPHONE mobile,U_LASTLOGINTIME lastlogin_time,ROWTIME AS create_time";
        $info   = $this->CI->user_model->get_one($where,'bl_user',$fields);
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $info;
    }
    
    /**
     * 用户反馈接口
     * @param type $params
     */
    public function do_feedback($params)
    {
        $this->CI->user_model->start();
        $name   = $this->get_userinfo($params)['name'];
        $data   = array(
            'F_USERIDX'     => $params['uuid'],
            'F_NICKNAME'    => $name,
            'F_INFO'        => $params['content'],
            'F_CONTACT'     => $params['contact'],
            'F_IP'          => $this->ip,
            'STATUS'        => 1,
        );
        $ist_res    = $this->CI->user_model->insert_data($data,'bl_feedback');
        if (!$ist_res) {
            $this->CI->user_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_FEEDBACK_INSERT_FAIL);
            $this->CI->output_json_return();
        }
        $this->CI->user_model->success();
        return true;
    }
    
    /**
     * 获取消息列表
     * @param type $params
     */
    public function get_message_list($params)
    {
        $user_info      = $this->get_userinfo($params);
        $sql            = "SELECT COUNT(IDX) AS num FROM bl_mailbox  where  STATUS = 0 AND  UNIX_TIMESTAMP(ROWTIME) >= ".strtotime($user_info['create_time'])." AND IDX NOT IN (SELECT M_MAILIDX FROM bl_mailbox_status WHERE M_USERIDX = ".$params['uuid'].")";
        $total_count    = $this->CI->user_model->exec_by_sql($sql);
        if (!$total_count['num']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count['num']/$params['pagesize']);
        $select             = "IDX AS id,M_NAME AS title,M_INFO AS content,M_SENDER AS sender,ROWTIME AS sender_time";
        $sql_2              = "SELECT ".$select." FROM bl_mailbox AS A where  STATUS = 0 AND  UNIX_TIMESTAMP(ROWTIME) >= ".strtotime($user_info['create_time'])." AND IDX NOT IN (SELECT M_MAILIDX FROM bl_mailbox_status WHERE M_USERIDX = ".$params['uuid'].") ORDER BY IDX DESC LIMIT ".$params['offset'].",".$params['pagesize'];
        $data['list']       = $this->CI->user_model->exec_by_sql($sql_2,true);
        return $data;
    }
    
    /**
     * 删除消息通知
     * @param type $params
     */
    public function do_message_del($params)
    {
        $table  = "bl_mailbox_status";
        $data   = array(
            'M_MAILIDX' => $params['id'],
            "M_USERIDX" => $params['uuid'],
            'M_STATUS'  => 1,
            'STATUS'    => 0,
        );
        $res    = $this->CI->user_model->insert_data($data,$table);
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_MESSAGE_DEL_FAIL);
            $this->CI->output_json_return();
        }
        return true;
    }
    
    
    /**
     * 获取百联账户的user_id
     */
    public function get_bluser_id($uuid)
    {
        $where          = array('U_USERIDX'=>$uuid,'status'=>0);
        $fields         = 'U_ACCOUNTID AS user_id,U_ACCOUNTNAME AS name';
        $register_info  = $this->CI->user_model->get_one($where,'bl_userlogin',$fields);
        if (!$register_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_REGISTER_INFO_FAIL);
            return false;
        }
        return $register_info;
    }
    
    
    /**
     * 更新用户信息
     */
    public function update_user_info($uuid,$fields)
    {
        $table  = "bl_user";
        $where  = array('IDX'=>$uuid,'STATUS'=>0);
        $res    = $this->CI->user_model->update_data($fields,$where,$table);
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_BEST_SCORE_FAIL);
            return false;
        }
        return true;
    }
    
    
    
}

