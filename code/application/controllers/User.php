<?php
/**
 * 用户模块
 * @author	huhong
 * @date	2016-08-24 16:07
 */
class User extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('user_lib');
    }
    
    /**
     * 快速登入
     */
    public function login()
    {
        $params['channel']  = $this->request_params('channel');
        $params['user_id']  = $this->request_params('user_id');
        $params['nickname'] = urldecode($this->request_params('nickname'));
        $params['mobile']   = $this->request_params('mobile');
        $params['sex']      = urldecode($this->request_params('sex'));
        $params['image']    = $this->request_params('image');
        $params['sign']     = $this->request_params('sign');
        
        // 校验参数
        if ($params['channel'] == "" || $params['user_id'] == "" || $params['nickname'] == '' || $params['sign'] == "") {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        // 校验签名
        $this->utility->check_sign($params, $params['sign']);
        
        $data   = $this->user_lib->do_login($params);
        $this->output_json_return($data);
    }
    
    
    /**
     * 注销账户
     */
    public function logout()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params, $params['sign']);
        $this->user_lib->do_logout($params);
        $this->output_json_return();
    }
    
    /**
     * 获取用户信息接口
     */
    public function user_info()
    {
        log_message('info', 'user_info:'.$this->user_lib->ip.'  params：'.http_build_query($_REQUEST));
        $params = $this->public_params();
        $this->utility->check_sign($params, $params['sign']);
        $data   = $this->user_lib->get_userinfo($params);
        $this->output_json_return($data);
    }
    
    /**
     * 用户反馈
     */
    public function feedback()
    {
        $params             = $this->public_params();
        $params['content']  = urldecode($this->request_params('content'));
        $params['contact']  = urldecode($this->request_params('contact'));
        if ($params['content'] == '' || $params['contact'] == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        if (mb_strlen($params['contact'],'utf8') > 50) {
            $this->error_->set_error(Err_Code::ERR_CONTACT_TOO_LONG);
            $this->output_json_return();
        }
        if (mb_strlen($params['content'],'utf8') > 500) {
            $this->error_->set_error(Err_Code::ERR_FEEDBACK_TOO_MUCH_CONTENT);
            $this->output_json_return();
        }
        $this->user_lib->do_feedback($params);
        $this->output_json_return();
    }
    
    /**
     * 消息列表
     */
    public function message_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->user_lib->get_message_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 删除消息
     */
    public function message_del()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->user_lib->do_message_del($params);
        $this->output_json_return();
    }
    
    public function add_mess()
    {
        $this->user_lib->add_messge();
    }
    
    
}
