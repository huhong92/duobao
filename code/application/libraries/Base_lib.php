<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Base_lib {
    protected $CI = NULL;
    protected $ts;
    public $ip;
    public $zeit;
    public $server_url  = 'http://mopenapi.st.iokbl.com/blgroup-openapi/service.htm?openapi_params=';// OenAPi数据接口地址
    public $h5_pay_url  = 'http://zf.st.iokbl.com/payment /payForApp.html';// H5收银台支付地址
    public $openapi_url_test = 'http://mopenapi.st.iokbl.com/blgroup-openapi/';
    // public $openapi_url_test = 'http://mopenapi.st.iokbl.com:443/blgroup-openapi/';
    public $openapi_url = 'http://mopenapi.bl.com/blgroup-openapi/';
    
    public $erp_url_test    = "http://180.168.251.245:8011/soa-infra/services/default/SIT_PLSQL_CUX_3_WS_SERVER_PRG/CUX_3_WS_SERVER_PRG_Service";
    public $erp_url         = "http://180.168.251.246:8011/soa-infra/services/default/QQDPRD_PLSQL_CUX_3_WS_SERVER_PRG/CUX_3_WS_SERVER_PRG_Service";
            
    function __construct() {
        $this->CI = &get_instance();
        $this->ip = $this->CI->input->ip_address();
        $this->zeit = date('Y-m-d H:i:s',time());
    }
    
    /**
    * @see CI_Loader::library
    * @param	string	$library	Library name
    * @param	array	$params		Optional parameters to pass to the library class constructor
    * @param	string	$object_name	An optional object name to assign to
    * @return	object
    */
   public function load_library($library, $params = NULL, $object_name = NULL){
           $object = $this->CI->load->library($library,$params,$object_name);
           if($object_name){
                   $alias_name  = strtolower($object_name);
           }else{
                   $alias_name  = strtolower($library);
           }
           $this->$alias_name   = $this->CI->$alias_name;
           return $object;
   }
   
   /**
    * @see CI_Loader::model
    * @param	string	$model		Model name
    * @param	string	$name		An optional object name to assign to
    * @param	bool	$db_conn	An optional database connection configuration to initialize
    * @return 	object
    */
    public function load_model($model, $name = '', $db_conn = FALSE){
           $object = $this->CI->load->model($model,$name,$db_conn);
           if($name){
                   $alias_name  = strtolower($name);
           }else{
                   $alias_name  = strtolower($model);
           }
           $this->$alias_name   = $this->ci->$alias_name;
           return $object;
    }
    
    /**
     * 保存MC
     */
    public function save_mc($key,$val,$ttl)
    {
        return $this->CI->cache->memcached->save($key, $val, $ttl);
    }
    
    /**
     * 获取MC
     */
    public function get_mc($key)
    {
        return $this->CI->cache->memcached->get($key);
    }
    
    /**
     * 删除MC
     */
    public function delete_mc($key)
    {
        return $this->CI->cache->memcached->delete($key);
    }
}
