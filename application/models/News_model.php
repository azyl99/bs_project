<?php
class News_model extends CI_Model
{
	/**
     * 构造函数
     * 按照application/config/database.php配置文件连接数据库
     */
	public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
	
	public function getNews($username) 
	{
		// 访问次数所占的权重，只有最近30次访问
		$sql = "select type, count(*) as cnt from (select type from visitlogs order by time desc limit 30) as temp group by type;";
		$query = $this->db->query($sql);
		$cnts = array();
		foreach ($query->result() as $row) {
			$cnts[$row->type] = 50*$row->cnt;//直接插入
		}
		// var_dump($cnts);
		
		// 偏好所占的权重
		$sql = "SELECT type FROM preferences WHERE user=?";
		$query = $this->db->query($sql, array($username));
		$prefs = array();
		foreach ($query->result() as $row) {
			$prefs[$row->type] = True;// 这个键值True没用
		}
		
		// 时间所占的权重
		$sql = "select * from news order by time desc limit 500;";
		$query = $this->db->query($sql);
		$news = array();
		$weights = array();
		$i = 1000;
		foreach ($query->result() as $row) {
			$w = 0;
			if (array_key_exists($row->type, $cnts))
				$w = $w + $cnts[$row->type];
			if (array_key_exists($row->type, $prefs))
				$w = $w + 400;
			$w = $w + $i;
			$weights[] = $w;
			$news[] = array("title"=>$row->title,"text"=>$row->text,"link"=>$row->link,
					"type"=>$row->type, "subtype"=>$row->subtype,"time"=>$row->time, "weight"=>round($w/30));// 精度为30
			$i = $i - 1;
		}
		array_multisort($weights, SORT_DESC, SORT_NUMERIC, $news);
		return $news;
	}
	
	public function getNewsByType($type, $subtype)
    {
		if ($subtype == '综合') {
			if ($type == '首页') {
				$sql = "select * from news order by time desc;";
				$query = $this->db->query($sql);
			}
			else {
				$sql = "select * from news where type = ? order by time desc;";
				$query = $this->db->query($sql, array($type));
			}
		}
		else {
			$sql = "select * from news where type = ? and subtype = ? order by time desc;";
			$query = $this->db->query($sql, array($type, $subtype));
		}
		//定义一个空的二维数组
		$a = array();
		foreach ($query->result() as $row) {
            // 往数组后面添加一项 a[i++] = xxx
            $a[] = array("title"=>$row->title,"text"=>$row->text,"link"=>$row->link,
					"type"=>$row->type, "subtype"=>$row->subtype,"time"=>$row->time);
        }
        return $a;
    }
	
	public function refresh_pref($user, $type)
	{
		$sql = "INSERT INTO visitLogs (user,type,time) VALUES (?,?,now())";
		return $this->db->query($sql, array($user, $type));
	}
	
	// 加载10条数据
	// public function getPage($page)
    // {
        // $sql = "select * from news order by time desc limit ?,?;";
		// $num = 10;

        // $query = $this->db->query($sql, array($page*$num, ($page+1)*$num));
		
		// foreach ($query->result() as $row)
        // {
            // $a[] = array("title"=>$row->title,"text"=>$row->text,"link"=>$row->link,
					// "type"=>$row->type, "subtype"=>$row->subtype,"time"=>$row->time);
        // }
        // return $a;
    // }
	
	
	
}