<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class MY_Model extends CI_Model {
	/**
	 * 
	 * @var Base_model
	 */
	public $_object = Array();
        public $zeit;
	
	public function __construct() {
		parent::__construct ();
		$this->load->database();
                $this->zeit = date('Y-m-d H:i:s', time());
	}
	
	/**
	 * @param string $EXIT
	 */
	public function printQuery($EXIT=TRUE){
		print_r($this->db->queries);
		if($EXIT==TRUE) exit;
	}
	
	/**
	 * 删除数据
	 *
	 * @param String $table        	
	 * @param Array $where        	
	 */
	public function delete_data(Array $where,$table) {
		$this->db->delete ( $table, $where );
		return $this->db->affected_rows ();
	}
	
	/**
	 * 采用replace insert操作新增或者更新语句
	 * 
	 * @param array $pairs
	 * @param string $table
	 * @return numbers
	 */
	public function replace_data(Array $pairs,$table){
		$this->db->set ( $pairs );
		return $this->db->replace ( $table );
	}
	
	
	/**
	 * 新增数据
	 * @param array $piars        	
	 * @param string $table
	 */
	public function insert_data( Array $pairs ,$table) {
            $this->db->set ( $pairs );
            if ($this->db->insert ( $table )) {
                    return $this->db->insert_id ();
            }
	}
	
	/**
	 * 提供一个带主索引(unique)特性
	 * 存在该记录则更新，不存在则插入记录的sql
	 * 
	 * @param array $pairs 需要操作的数据聚合
	 * @param string $key 更新的字段key，请确保在$pairs中有此key
	 * @param string $table 
	 * 
	 * @return boolean
	 */
	public function insert_duplicate(Array $pairs,$key,$table){
		$this->db->set($pairs);
		$insert_sql = $this->db->insert_string($table,$pairs);
		$insert_sql.= " ON DUPLICATE KEY UPDATE `$key` = '{$pairs[$key]}'";
		return $this->db->query($insert_sql);
	}
	
	/**
	 * 插入多列数组
	 * @param unknown $table
	 * @param unknown $pairs
	 */
	protected function insert_batch(Array $pairs,$table){
		return $this->db->insert_batch($table,$pairs);
	}
	
	/**
	 * 修改数据
	 *
	 * @param string $table        	
	 * @param array $pairs        	
	 * @param array $where        	
	 * 
	 * @return boolean
	 */
	public function update_data(Array $pairs, Array $where,$table) {
		return $this->db->update ( $table, $pairs, $where );
	}
	
	/**
	 * 获得一条数据
	 * 
	 * @param array         $table
	 * @param array|string  $where
	 * @param string        $fields
	 */
	public function get_one($where, $fields,$table) {
		$this->db->select ( $fields );
		$this->db->where ( $where );
		$this->db->limit ( 1 );
		$query = $this->db->get ( $table );
		return $query->row_array ();
	}
	
	/**
	 * 获得指定列的信息
	 *
	 * @param maxid $table
	 * @param maxid $where
	 * @param maxid $limie
	 * @param maxid $fields
	 * @return array:
	 */
	public function get_list(Array $where, Array $limit = array('size'=>10,'page'=>0), $fields = "*",$table) {
		$this->db->select ( $fields );
		$this->db->where ( $where );
		$this->db->limit ( $limit ['size'], $limit ['page'] * $limit ['size'] );
		$query = $this->db->get ( $table );
		return $query->result_array ();
	}
	
	
	/**
	 * 检测数据是否已经存在
	 * 
	 * @param unknown_type $table        	
	 * @param 关键计算，默认为* $key        	
	 * @param unknown_type $where        	
	 * @return boolean
	 */
	public function is_already($key, Array $where,$table) {
		$result = FALSE;
		$this->db->select ( "count({$key}) as total" );
		$this->db->where ( $where );
		$query = $this->db->get ( $table );
		$row = $query->row_array ();
		if ($row ["total"] > 0) {
			$result = TRUE;
		}
		return $result;
	}
	
	/**
	 * select count(key) from table where ..
	 * @param string $table
	 * @param string $key
	 * @param array $where
	 */
	public function count($key,Array $where,$table){
		$this->db->select ( "count({$key}) as total" );
		$this->db->where ( $where );
		$query = $this->db->get ( $table );
		$row = $query->row_array ();
		return $row['total'];
	}
	
	/**
	 * 
	 * @param string $table
	 * @param array $data
	 * @param string $field
	 */
	public function update_batch(Array $data, $field,$table){
		return $this->db->update_batch($table, $data, $field);
	}
	
	/**
	 * 带条件的复合查询
	 * @param Array $options
	 * <br>|- where array
	 * <br>|- limit
	 * <br>|- order
	 * <br>|- fileds
	 * @return Array
	 */
	public function get_list_term(Array $options,$table){
		if(key_exists("where",$options)) $this->db->where($options['where']);
		if(key_exists("order",$options)) $this->db->order_by($options['order']);
		//可以传入第三个可选的参数来控制 LIKE 通配符（%）的位置，可用选项有：'before'，'after' 和 'both' (默认为 'both')。
		if(key_exists("like",$options)){
			$like = $options["like"];
			$side = "both";
			if(key_exists("side",$like)){
				$side = $like["side"];
			}
			$this->db->like($like["field"],$like["match"],$side);
		}
		if(key_exists("limit",$options)){
			$limit = $options['limit'];
			$this->db->limit($options['limit']['size'],$options['limit']['page']);
		}
                if(key_exists("groupby",$options)){
			$this->db->group_by($options['groupby']);
		}
		if(key_exists("fields",$options)){
			$this->db->select($options['fields']);
		}else{
			$this->db->select("*");
		}
		$query = $this->db->get($table);
// 		$this->printQuery();
		return $query->result_array();
	}
	
	/**
	 * 通过查询语句，获得数据列表记录
	 * 
	 * @param string $SQL
	 * @param string $type. row/result
	 */
	public function fetch($SQL,$type="row"){
		$query = $this->db->query($SQL);
                if ($type == 'update' || $type == 'delete' || $type == 'insert') {
                    return $query;
                }
		if($type=="row"){
			return $query->row_array();
		}else{
			return $query->result_array();
		}
	}
	
	/**
	 * 获得列表查询SQL语句
	 */
	public function get_list_term_query(Array $options,$table){
		if(key_exists("where",$options)) $this->db->where($options['where']);
		if(key_exists("order",$options)) $this->db->order_by($options['order']);
		
		//可以传入第三个可选的参数来控制 LIKE 通配符（%）的位置，可用选项有：'before'，'after' 和 'both' (默认为 'both')。
		if(key_exists("like",$options)) $this->db->like($options["like"]);
		
		if(key_exists("limit",$options)){
			$limit = $options['limit'];
			$this->db->limit($options['limit']['size'],$options['limit']['page']);
		}
		
		if(key_exists("fileds",$options)){
			$this->db->select($options['fileds']);
		}else{
			$this->db->select("*");
		}
		return $this->db->get_compiled_select($table,FALSE);
	}
	
	/**
	 * 判断指定字段及条件语句中的项目是否已经存在
	 * 
	 * @param string $key
	 * @param array $where
	 * @param string $table
	 * @return boolean
	 */
	public function chkFieldsExist($key,$where=array(),$table){
		$result = FALSE;
		$this->db->select("count({$key}) as total ");
		foreach ($where as $key=>$val){
			$this->db->where($key,$val);
		}
		$query = $this->db->get($table);
		$row = $query->row_array();
		if( $row["total"]>0 ){
			$result = TRUE;
		}
		return $result;
	}
	
	public function set($name,$value){
		$this->_object[$name] = $value;		
	}
	public function get($name){
		return $this->_object[$name];
	}
        
        /**
         * @param string $condition 查询条件（包含 “WHERE IDX = 1  LIMIT 1，10 ORDER BY IDX”）
         * @param string $select = "a.aaa as a, b.bbb as b "
         * @param string $table = "aa as a, bbb as b"
         * 联表查询
         */
        public function union_select($condition, $table, $select = "*")
        {
            $sql = "SELECT ".$select." FROM ".$table.$condition;
            $res = $this->db->query($sql);
            return $res;
        }
        
        /**
         * LEFT JOIN
         */
        public function get_composite_row_array($condition, $join_condition,$select,$tb_a,$tb_b,$batch = FALSE)
        {
            if ($condition == '' || $select=='' || $join_condition == '' || $tb_a == '' || $tb_b == '') {
                $this->CI->output_json_return(Err_Code::ERR_PARA);
                return false;
            }
            $_limit = '';
            if($batch === false) {
                $_limit = " LIMIT 1";
            }
            $sql = "SELECT ".$select." FROM ".$tb_a." LEFT JOIN ".$tb_b." ON ".$join_condition." WHERE ".$condition.$_limit;
            $query = $this->db->query($sql);
            // 记录数据库错误日志
            if ($query === false) {
                return false;
            }
            $ret = array();
            if ($query->num_rows() > 0) {
                $ret = $query->result_array();
                if($batch === false) {
                    $ret = $ret[0];
                }
            }
            return $ret;
        }
        
        /**
         * 查询数据总条数
         * @param type $sql
         */
        public function exec_by_sql($sql,$batch = FALSE)
        {
            $query  = $this->db->query($sql);
            if ($query === false) {
                return false;
            }
            $ret = array();
            if ($query->num_rows() > 0) {
                $ret = $query->result_array();
            }
            if (!$batch) {
                return $ret[0];
            }
            return $ret;
        }
        
        /**
         * 开启事务
         */
        public function start()
        {
            $this->db->trans_begin();
        }
        /**
         * 事务回滚
         */
        public function error()
        {
            $this->db->trans_rollback();
            return false;
        }
        /**
         * 提交事务
         */
        public function success()
        {
            $this->db->trans_commit();
            return true;
        }
}
