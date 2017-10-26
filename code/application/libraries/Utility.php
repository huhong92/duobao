<?php
/**
 * 公共帮助类库文件
 * author huhong
 * date 2016-05-04
 */
class Utility {
    private $CI;
    
    public function __construct() {
        $this->CI = & get_instance();
    }
    
    /**
     * 校验参数
     * @param array $params
     * @param string $sign
     * @return string
     */
    public function check_sign($params, $sign)
    {
        $get_sign = $this->get_sign($params);
        if (ENVIRONMENT != 'development') {
            if ($get_sign != $sign) {
                log_message('error', 'sign_err:'.$this->CI->input->ip_address().','.$get_sign.',签名错误');
                $this->CI->error_->set_error(Err_Code::ERR_PARAM_SIGN);
                $this->CI->output_json_return();
            }
        }
        return true;
    }
    
    /**
     * 获取参数校验值
     * @param array $params
     * @return string 校验值
     */
    public function get_sign($params)
    {
        foreach ($params as $key => $val) {
            if ($key == "sign" || ($val === "") || $key == 'sign_key' || $key == 'sign_recive_type') {
                continue;
            }
            $para[$key] = $params[$key];
        }
        ksort($para);
        $arg = '';
        foreach ($para as $k=>$v) {
            $arg .= $k.'='.$v.'&';
        }
        $sign_key = $this->CI->passport->get('sign_key');
        $arg .= 'key='.$sign_key;
        return md5($arg);
    }
    
    /**
     * 百联数据业务接口sign计算方式
     */
    public function get_blsign($params)
    {
        $params['salt'] = $this->CI->passport->get('bl_salt');
        return md5($params['access_token'].$params['service_name'].$params['timestamp'].$params['salt']);
    }
    
    /**
     * rsa加密获取sign
     */
    public function sha1_sign($params,$token_key)
    {
        $params['salt'] = $this->CI->passport->get('bl_salt');
        return sha1($token_key.$params['service_name'].$params['timestamp'].$params['salt'].$params['sn'].$params['channelId']);
    }
    
    //访问外部地址（POST方式）
    function post($url, $post_data = array(),$header = array()) {
        if (is_array($post_data)) {
            $qry_str = http_build_query($post_data);
        } else {
            $qry_str = $post_data;
        }
        if (!$header) {
            $header = array();
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '15');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);
        $content = trim(curl_exec($ch));
        curl_close($ch);
        return $content;
    }
    
    //get 方式
    public function get($url, $fields = array()) {
        if (is_array($fields)) {
            $qry_str = http_build_query($fields);
        } else {
            $qry_str = $fields;
        }
        if (trim($qry_str) != '') {
            $url = $url . '?' . $qry_str;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '100');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = trim(curl_exec($ch));
        curl_close($ch);
        return $content;
    }
    
    /**
     * 获取用户基础信息
     */
    public function get_user_info($uuid,$fields = '')
    {
        $this->CI->load->library('user_lib');
        $user_info  = $this->CI->user_lib->get_userinfo(array('uuid'=>$uuid));
        if (!$user_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        if ($fields) {
            return $user_info[$fields];
        }
        return $user_info;
    }
    
    /**
     * 将XML转为数组
     */
    public function xml_to_array($xml)
    {
        // $xml = "<xml><aa><![CDATA[aaa ]]></aa><c><ddd>sdsdsd</ddd><eee>dsfsdf</eee></c><b>eeee</b></xml>";
        $xml_obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml_arr = json_decode(json_encode($xml_obj),TRUE);
        return $xml_arr;
    }
    
    /**
     * 将字符串转换成二进制
     * @param type $str
     * @return type
     */
    public function str_to_bin($str)
    {
       $arr  = preg_split('/(?<!^)(?!$)/u',$str);
       foreach ($arr as &$v) {
           $temp = unpack('H*', $v);
           $v = base_convert($temp[1], 16, 2);
           unset($temp);
       }
       return join(' ',$arr);
    }
    
    /**
     * 讲二进制转换成字符串
     * @param type $str
     * @return type
     */
    public function bin_to_str($str)
    {
        $arr = explode(' ', $str);
        foreach($arr as &$v){
            $v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }
    
    public static function emoji_to_html($str) {  
        $regex = '/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?|[\x{1F900}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?/u';  
        $str = preg_replace_callback($regex,function($matches){  
            $str = json_encode($matches[0]);  
            $str = '<em data-emoji=' . str_replace('\u', 'em:', $str) . '></em>';  
            return $str;  
        },$str);  
        return $str;  
    }  
    
    /** 
     * 输出emoji表情 
     * @param $matches 
     * @return mixed 
     */  
    public static function preg_emoji($matches)  
    {  
        $str = $matches[0];  
        $str = str_replace('em:', '\u', $str);  
        return $str;  
    } 
    
    /**
     * 生成15位的唯一数（前缀+15位随机数）
     * @param type $prefix
     * @return type
     */
    public function get_uniqid($prefix)
    {
        return $prefix.uniqid().rand(10, 99);
    }
    
    //是否为手机号
    function is_mobile($val) {
        if (!preg_match('/^(13|14|15|17|18)\d{9}$/', $val)) {
            return false;
        }
        return true;
    }
    
    
}

