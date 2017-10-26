<?php
/**
 * 一元夺宝模块
 * @author	huhong
 * @date	2016-12-16 15:09
 */
class Duobao extends MY_Controller {
    public $non_login = 0;
    public function __construct() {
        parent::__construct();
        $this->load->library('duobao_lib');
    }
    
    /**
     * 测试方法
     */
    public function test()
    {
        $prefix         = $this->passport->get('duobao_no');
        $params['id']   = $this->request_params('id');// 上架商品
        $g_info         = $this->duobao_lib->get_goods_info($params);
        $total_num      = 100;// 总股数
        $value['total_dbno']    = range(10000001,$total_num+10000000);
        $value['allo_dbno']     = array();
        // 模拟上架夺宝商品
        $res = $this->cache->memcached->save($prefix.$g_info['date_no'], json_encode($value),0);
        $aa = $this->cache->memcached->get($prefix.$g_info['date_no']);
        var_dump($aa);exit;
    }

    /**
     * 夺宝商品列表
     * @params int type 列表类型,必须（1人气2最新3进度）
     * @params int cate_type 分类类型，可选 （默认全部分类）
     * @return boolean
     */
    public function goods_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        $params['type']     = (int)$this->request_params('type');
        $params['cate_type']= $this->request_params('cate_type');
        if ($params['offset'] < 0 || !$params['type']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->duobao_lib->get_goods_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 夺宝商品详情
     */
    public function goods_info()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_goods_info($params);
        $this->output_json_return($data);
    }
    
    /**
     * 查看当前用户可参与本期夺宝次数
     */
    public function allow_dbnum()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_allow_dbnum($params);
        $this->output_json_return($data);
    }
    
    /**
     * 参与夺宝
     */
    public function take_order()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        $params['num']  = (int)$this->request_params('num');
        if (!$params['id'] || !$params['num']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->do_take_order($params);
        $this->output_json_return($data);
    }
    

    /**
     * 夺宝最新揭晓列表
     * @param int $type 列表类型,必须（1进行2已揭晓 3全部）
     * @return boolean
     */
    public function publish_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        $params['type']     = (int)$this->request_params('type');
        if ($params['offset'] < 0 || !$params['type']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->duobao_lib->get_publish_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 夺宝商品揭晓详情
     * @param int $id 商品ID
     * @return array 商品信息
     */
    public function publish_info()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_publish_info($params);
        $this->output_json_return($data);
    }
    
    /**
     * 我的夺宝记录
     * @param  int type 列表类型,必须（1进行2已揭晓 3全部）
     * @return boolean
     */
    public function mypublish_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        $params['type']     = (int)$this->request_params('type');
        if ($params['offset'] < 0 || !$params['type']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->duobao_lib->get_mypublish_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 订单查询
     */
    public function dborder_info()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_dborder_info($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取夺宝商品参与历史记录
     * @param int id 夺宝商品ID 
     * @return boolean
     */
    public function dbhistory()
    {
        $params             = $this->public_params(0);
        $params['id']       = (int)$this->request_params('id');
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if (!$params['id'] || $params['offset'] < 0) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->duobao_lib->get_dbhistory($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取夺宝消息快报
     */
    public function dbmessage()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_dbmessage($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取夺宝商品分类列表
     */
    public function type_list()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_type_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 商品期号绑定地址
     * @param int $id 商品期号表id
     * @param int $address_id 地址id
     */
    public function bind_address()
    {
        $params                 = $this->public_params();
        $params['id']           = (int)$this->request_params('id');
        $params['address_id']   = (int)$this->request_params('address_id');
        if (!$params['id'] || !$params['address_id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->duobao_lib->do_bind_address($params);
        $this->output_json_return();
    }
    
    /**
     * 获取地址列表
     */
    public function address_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
         $this->error_->set_error(Err_Code::ERR_DB_NO_DATA);
         $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->duobao_lib->get_address_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 添加地址
     */
    public function add_address()
    {
        $params             = $this->public_params();
        $params['receive']  = urldecode($this->request_params('receive'));
        $params['mobile']   = $this->request_params('mobile');
        $params['address']  = urldecode($this->request_params('address'));
        if (!$params['receive'] || !$params['mobile'] || !$params['address']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        if (!$this->utility->is_mobile($params['mobile'])) {
            $this->error_->set_error(Err_Code::ERR_MOBILE_FOMAT_FAIL);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->duobao_lib->do_add_address($params);
        $this->output_json_return();
    }
    
    /**
     * 修改收货信息
     */
    public function update_address()
    {
        $params             = $this->public_params();
        $params['id']       = (int)$this->request_params('id');
        $params['receive']  = urldecode($this->request_params('receive'));
        $params['mobile']   = $this->request_params('mobile');
        $params['address']  = urldecode($this->request_params('address'));
        $params['is_use']   = (int)$this->request_params('is_use');
        if (!$params['id'] || !$params['is_use']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        if ($params['mobile'] && !$this->utility->is_mobile($params['mobile'])) {
            $this->error_->set_error(Err_Code::ERR_MOBILE_FOMAT_FAIL);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->duobao_lib->do_update_address($params);
        $this->output_json_return();
    }
    
    /**
     * 删除地址信息
     */
    public function del_address()
    {
        $params             = $this->public_params();
        $params['id']       = $this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->duobao_lib->do_del_address($params);
        $this->output_json_return();
    }
    
    /**
     * 获取用户夺宝号
     */
    public function duobao_nos()
    {
        $params         = $this->public_params();
        $params['id']   = $this->request_params('id');
        $this->utility->check_sign($params,$params['sign']);
        $data           = $this->duobao_lib->get_duobao_nos($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取商品相关推荐列表
     */
    public function recomment_list()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id']  == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->get_recomment_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 查询物流号
     */
    public function query_logistics()
    {
        $params                 = $this->public_params();
        $params['id']           = (int)$this->request_params('id');
        if (!$params['id']) {
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->duobao_lib->do_query_logistics($params);
        $this->output_json_return($data);
    }
    
    
}
