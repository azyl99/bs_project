<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url', 'cookie'));
        $this->load->library(array('form_validation','session'));
        $this->load->model("news_model");
		$this->load->model("user_model");
		$this->page = 0;
    }
	
	public function index()
	{
		$type = isset($_GET['type'])? $_GET['type']:'首页';
		$subtype = isset($_GET['subtype'])? $_GET['subtype']:'综合';
		
		$username = Null;
		if (isset($_SESSION['username']))
			$username = $_SESSION['username'];
		if ($username && $type != '首页') {
			$this->news_model->refresh_pref($username, $type);// 更新权重
		}
		
		if($username && $type == '首页') {
			$news_array = $this->news_model->getNews($username);// 访问首页时使用权重排序
		} else {
			$news_array = $this->news_model->getNewsByType($type, $subtype);
		}
		$data['news_array'] = $news_array;
		$data['type'] = $type;
		$data['subtype'] = $subtype;
		$this->load->view('index.html', $data);
	}
	
	// public function press()
	// {
		// $news_array = $this->news_model->getNews2('新闻');// 默认1000条
		// $data['news_array'] = $news_array;
		// $data['active'] = 'press';
		// $this->load->view('index.html', $data);
	// }
	
	// public function nextPage() 
	// {
		// ++$this->page;
		// $news_array = $this->news_model->getPage($this->page);
		// $data['news_array'] = $news_array;
	// }
}
