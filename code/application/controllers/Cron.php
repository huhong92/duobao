<?php
class Cron extends MY_Controller{
    
    /**
     * 定期执行开奖操作(每分钟执行一次)
     */
    public function exec_dbopen()
    {
        $this->load->library('duobao_lib');
        $res = $this->duobao_lib->dbopen();
        echo $res;
    }
    
}

