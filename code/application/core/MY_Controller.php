<?php
/**
 * 基础Controller
 * @author huhong
 * @date 2016-05-10
 */
class MY_Controller extends CI_Controller{
    const PAGESIZE = 20;
    
    function __construct() {
        parent::__construct();
        $this->load->driver('cache',array( 'adapter'=>'file', 'backup'=>'memcached'));
        $this->ip = $this->input->ip_address();
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * jason格式输出
     * @param array $data
     */
    public function output_json_return($data = array()) {
        header('Content-type: application/json;charset=utf-8');
        $code = $this->error_->get_error();
        if ($code == null) {
            $this->error_->set_error(Err_Code::ERR_OK);
            $code = $this->error_->get_error();
        }
        if(empty($data)){
           $data = new stdClass();
        }
        echo json_encode(array('c' => $this->error_->get_error(), 'm' => $this->error_->error_msg(),'data' => $data));exit;
    }
    

    /**
     * 获取公共参数
     * @return array 公共参数值
     * @param int $is_login 改接口是否必须登录（0否 1是）
     * @return type
     */
    public function public_params($is_login = 1)
    {
        $params['channel']  = (int)$this->request_params('channel');
        $params['uuid']     = (int)$this->request_params('uuid');
        $params['token']    = $this->request_params('token');
        $params['sign']     = $this->request_params('sign');
        // 校验参数
        if ($params['channel'] == '' || $params['sign'] == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        if ($is_login) {
            if (!$params['uuid'] || !$params['token']) {
                $this->error_->set_error(Err_Code::ERR_MUST_DO_LOGIN_FAIL);
                $this->output_json_return();
            }
        }
        if ($params['uuid'] && $params['token']) {
             // 校验TOKEN是否有效
//            if (!$this->is_login($params['uuid'],$params['channel'], $params['token'])) {
//                $this->error_->set_error(Err_Code::ERR_TOKEN_EXPIRE);
//                $this->output_json_return();
//            }
        }
        return $params;
    }
    
    /**
     * 校验登录TOKEN是否有效 判断是否登录
     * @param int $uuid
     * @return  bool 
     */
    public function is_login($uuid,$channel,$token)
    {
        $token_key  = $this->passport->get('token_key');
        $key        = $uuid."_".$channel."_".$token_key;
        $token_info = $this->get_token($key);
        if (!$token_info['token']) {
            return false;
        }
        if ($token_info['token'] != $token) {
            return false;
        }
        if (time() > $token_info['token_expire'] + $token_info['login_ts']) {
            return false;
        }
        return true;
    }

    /**
     * POST|GET接受数据
     * @param string $key 参数key
     * @return string 参数值
     */
    public function request_params($key)
    {
        if ($key == '') {
            return false;
        }
        $p = $this->input->get_post($key, true);
        if (is_array($p)) {
            return $p;
        }
        return trim($p);
    }
    
    /**
     * 生成登录TOKEN
     */
    public function gen_login_token($uuid,$channel)
    {
        $token_key      = $this->passport->get('token_key');
        $token_expire   = $this->passport->get('token_expire');
        
        $login_ts                   = time();
        $key                        = $uuid."_".$channel."_".$token_key;
        $item['token']              = md5($key."_".$login_ts);
        $item['login_ts']           = $login_ts;
        $item['token_expire']       = $token_expire;
        $item['login_expire_ts']    = $login_ts + $token_expire;
        $res = $this->cache->memcached->save($key, $item, $token_expire);
        if ($res) {
            return $item['token'];
        }
        $this->error_->set_error(Err_Code::ERR_TOKEN_SET_FAIL);
    }
    
    /**
     * 获取token信息
     */
    public function get_token($key)
    {
        return $this->cache->memcached->get($key);
    }
    
    /**
     * 删除TOKEN
     */
    public function delete_token($key)
    {
        return $this->cache->memcached->delete($key);
    }
}

