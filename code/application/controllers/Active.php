<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Active extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('active_lib');
    }
    
    /**
     * 获取活动列表
     */
    public function active_list()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->active_lib->get_active_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取活动详情
     */
    public function active_info()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->active_lib->get_active_info($params);
        $this->output_json_return($data);
    }
    
    
    
    
    
}
