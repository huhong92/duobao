<?php
/**
 * 一元夺宝表相关操作
 * @author	huhong
 * @date	2016-08-24 16:11
 */
class Duobao_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 插入单条数据
     * @param array  $data   插入数据
     * @param string $table  插入表
     * @return int         
     */
    public function insert_data(array $data, $table) {
        $data['ROWTIME']        = $this->zeit;
        $data['ROWTIMEUPDATE']  = $this->zeit;
        return parent::insert_data($data, $table);
    }
    
    /**
     * 插入多条数据
     * @param array  $data   插入数据
     * @param string $table  插入表
     * @return type
     */
    public function insert_batch(array $data, $table) {
        foreach ($data as &$v) {
            $v['ROWTIME']        = $this->zeit;
            $v['ROWTIMEUPDATE']  = $this->zeit;
        }
        return parent::insert_batch($data, $table);
    }
    
    /**
     * 更新单条数据
     * @param array  $fields  更新字段
     * @param array  $where   更新条件
     * @param string $table   更新表
     * @return int
     */
    public function update_data(array $fields, array $where, $table) {
        $fields['ROWTIMEUPDATE']  = $this->zeit;
        return parent::update_data($fields, $where, $table);
    }
    
    /**
     * 更新多条数据
     * @param array  $data  更新的数据
     * @param string $field 数组中的条件字段
     * @param type   $table 更新表
     * @return int
     */
    public function update_batch(array $data, $field, $table) {
        foreach ($data as &$v) {
            $v['ROWTIME']        = $this->zeit;
            $v['ROWTIMEUPDATE']  = $this->zeit;
        }
        return parent::update_batch($data, $field, $table);
    }
    
    /**
     * 获取单条数据
     * @param array|string  $where  获取数据条件
     * @param string        $fields 获取字段
     * @param string        $table  查询表
     * @return array
     */
    public function get_one($where, $table, $fields = "*") {
        return parent::get_one($where, $fields, $table);
    }
    
    /**
     * 获取多条数据
     * @param array     $options
     * @param string    $table
     * @return array
     */
    public function list_data($options,$table)
    {
        return parent::get_list_term($options, $table);
    }
    
    /**
     * 获取数据总条数
     * @param string|array  $where
     * @param string        $table
     * @param string        $key
     * @return int
     */
    public function total_count($where, $table, $key = "IDX")
    {
        return parent::count($key, $where, $table);
    }
    
    /**
     * 删除数据
     * @param array     $where
     * @param string    $table
     * @return int
     */
    public function delete_data(array $where, $table) {
        return parent::delete_data($where, $table);
    }
    
    /**
     * sql语句查询
     * @param string $SQL
     * @param string $type
     * @return int
     */
    public function fetch($SQL, $type = "row") {
        return parent::fetch($SQL, $type);
    }
    
    /**
     * 执行LEFT JOIN查询语句
     * @param string $condition
     * @param string $join_condition
     * @param string $select
     * @param string $tb_a
     * @param string $tb_b
     * @param bool   $batch
     */
    public function left_join($condition, $join_condition,$select,$tb_a,$tb_b,$batch = FALSE)
    {
        return parent::get_composite_row_array($condition, $join_condition, $select, $tb_a, $tb_b,$batch);
    }
    
    /**
     * 查询数据总条数
     */
    public function exec_by_sql($sql,$batch = false)
    {
        return parent::exec_by_sql($sql,$batch);
    }
}
