<?php
/**
 * 活动操作
 * @author	huhong
 * @date	2017-03-16 16:17
 */
class Active_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('active_model');
    }
    
    /**
     * 获取活动列表
     * @param type $params
     */
    public function get_active_list($params)
    {
        $table  = "bl_active";
        $options['where']   = array('STATUS'=>0);
        $options['fields']  = "IDX AS id,A_TYPE type,A_TITLE title,A_TOPIMG topimg,A_IMG img,A_INFO info,A_TYPE type,A_GOODSIDX g_idx";
        $data['list']       = $this->CI->active_model->list_data($options,$table);
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $path   = $this->CI->passport->get('source_url');
        foreach ($data['list'] as $k=>$v) {
            $data['list'][$k]['goods_id']   = 0;
            // 判断是否展示 推荐夺宝商品
            if ($v['type'] == 2) {// 活动类型1普通活动2进入商品
                $goods_id   = $this->get_dateno_by_goodsidx($v['g_idx']);
                if ($goods_id) {
                    $data['list'][$k]['goods_id']   = $goods_id;
                }
            }
            $data['list'][$k]['img']    = $path.$v['img'];
            $data['list'][$k]['topimg'] = $path.$v['topimg'];
            unset($data['list'][$k]['g_idx']);
        }
        return $data;
    }
    
    /**
     * 获取活动信息
     * @param type $params
     */
    public function get_active_info($params)
    {
        $table  = "bl_active";
        $where  = array('IDX'=>$params['id'],'STATUS'=>0);
        $fields ="IDX id,A_TYPE type,A_TITLE title,A_INFO info,A_TOPIMG topimg,A_IMG img,A_GOODSIDX goods_idx";
        $data   = $this->CI->active_model->get_one($where,$table,$fields);
        if (!$data) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $path           = $this->CI->passport->get('source_url');
        $data['img']    = $path.$data['img'];
        $data['topimg'] = $path.$data['topimg'];
        return $data;
    }
    

    /**
     * 通过商品配置表获取 当前正在夺宝的期号 的IDX
     * @param type $goods_idx 商品配置表自增IDX
     */
    public function get_dateno_by_goodsidx($goods_idx)
    {
        // 获取最近一期正在参与的夺宝
        $table  = "bl_dbgoods";
        $sql    = "SELECT IDX id,G_DATENO date_no,G_BUYSTATUS b_status FROM ".$table." WHERE G_GOODSIDX = ".$goods_idx." AND STATUS = 0 ORDER BY IDX LIMIT 1";
        $g_info = $this->CI->active_model->exec_by_sql($sql);
        if (!$g_info) {
            return false;
        }
        if ($g_info['b_status'] == 1) {// 购买状态（1进行中 [购买中] 2等待开奖3已揭晓)
            return $g_info['id'];
        }
        return false;
    }
}

