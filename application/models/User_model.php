<?php
class User_model extends CI_Model
{
	private $salt = "bs_project";
	/**
     * 构造函数
     * 按照application/config/database.php配置文件连接数据库
     */
	public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
	
	// 用户注册
	function register($username, $password, $email) 
	{
		$sql = "INSERT INTO users(user, password, email) VALUES(?, ?, ?)";
        $password_crypted = crypt($password, $this->salt);
        return $this->db->query($sql, array($username, $password_crypted, $email));
	}
	
	// 用户登录
	function login($username, $password) 
	{
		$sql = "SELECT * FROM users WHERE user=?";
        $query = $this->db->query($sql, array($username));
        $row = $query->row();
        return crypt($password, $this->salt) == $row->password;
	}
	
	// 设置偏好
	function set_preference($username, $preferences) 
	{
		$sql = "DELETE FROM preferences WHERE user=?";
		$query = $this->db->query($sql, array($username));
		$sql = "INSERT INTO preferences(user,type) VALUES(?, ?)";
		if (!isset($preferences))
			return ;
		foreach ($preferences as $preference){
			$query=$this->db->query($sql, array($username, $preference));
		}
	}
	
	// 查询用户偏好
	function get_preference($username) 
	{
		$sql = "SELECT type FROM preferences WHERE user=?";
		$query = $this->db->query($sql, array($username));
		
		$a = array();
		foreach ($query->result() as $row) {
			array_push($a, $row->type);
		}
		return $a;
	}
	
	function existUserName($name)
    {
        $sql = "SELECT * FROM users WHERE user = ? limit 1" ;
        return $this->db->query($sql, array($name))->num_rows() == 1;
    }
	// 用户名密码是否匹配
	public function validate($username, $password)
    {
        $sql = "SELECT * FROM users WHERE user=?";

        $query = $this->db->query($sql, array($username));
        $row = $query->row();
        return crypt($password, $this->salt) == $row->password;
    }
	
	function existEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? limit 1" ;
        return $this->db->query($sql, array($email))->num_rows() == 1;
    }
}