<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('duobao_lib');
    }
    
    public function index()
    {
        var_dump(333);exit;
        $params['sn']       = 'A7CF1B51-0F93-411A-82DD-E37FCE2F6568';
        $params['sign']     = 'e2250319814d2c33f2c1850e1e500f72';
        $params['channel']  = "1";
        $params['user_id']  = '100000000031450';
        $params['passport_id']  = "82131cf73d_1450";
        var_dump(json_encode($params));exit;
        
        // $list   = $this->test_lib->test_();
        var_dump($list);exit;
        phpinfo();
    }
    
    
    
    public function ssc()
    {
        $ssc_list   = $this->duobao_lib->sscopen_info(1);
        // $ssc_list   = $this->duobao_lib->ssc_luckno();
        // $ssc_info   = $ssc_list['data'][0];
        var_dump($ssc_list);exit;
    }
}
