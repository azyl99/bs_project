<?php

class User extends CI_Controller
{
	private $temp_user = 'what?';
	
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url', 'cookie'));
        $this->load->library(array('form_validation','session'));
        $this->load->model("user_model");
    }
	
    public function login()
    {
		$this->temp_user = $this->input->post("username");
		$data['type'] = '用户';
		$data['subtype'] = '登录';
		
        $this->form_validation->set_rules('username', '用户名', 'callback_username_exist');
        $this->form_validation->set_rules('password', '密码', 'required|callback_validate',
            array('required' => '{field}不能为空'));
		
		// 错误
        if ($this->form_validation->run() == FALSE)
        {
            $this->load->view('login.html', $data);
        }
		else
        {
            $username  = $this->input->post("username");
            $this->session->set_userdata("username", $username);
            redirect(site_url('news'));
        }
    }
	
	public function register()
	{
		$data['type'] = '用户';
		$data['subtype'] = '注册';
		
		$this->form_validation->set_rules('username', 'username', 'callback_username_check');
		$this->form_validation->set_rules('password', 'password', 'trim|callback_password_check');
		$this->form_validation->set_rules('password2', 'password2', 'required|matches[password]',
			array('required' => '确认密码不能为空', 'matches' => '两次输入的密码不一致'));
		$this->form_validation->set_rules('email', 'email', 'required|valid_email|callback_email_exist',
			array('required' => '邮箱不能为空', 'valid_email' => '邮箱格式错误'));
	
		/**
		* 如果输入的用户名、密码、第二次密码不合法，则重新加载注册界面
		*     显示用户名、密码、第二次密码不合法的提示信息
		* 如果输入的用户名、密码、第二次密码合法，则向数据库之中插入账号信息
		*/
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->view('register.html', $data);
		}
		else
		{
			// 将新的用户信息插入数据库，并显示主界面
			$this->user_model->register($this->input->post('username'), $this->input->post('password'));
			// 设置session key, 并加载到用户界面
			$this->session->set_userdata("username", $this->input->post('username'));
            redirect(site_url('news'));
		}
	}	
	public function logout()
	{
		$this->session->unset_userdata("username");
		redirect(site_url('news'));
	}
	
	public function preference()
	{
		$data['type'] = '用户';
		$data['subtype'] = '偏好设置';
		if (!isset($_SESSION['username'])) {
			echo '<p>请先<a href="'.site_url('user/login').'">登录</a></p>';
			return;
		}
		
		$username = $_SESSION['username'];
		
		$preferences = $this->input->post('options[]');
		if (isset($preferences)) {
			$preferences = $this->input->post('options[]');
			$this->user_model->set_preference($username, $preferences);
		}
		
		$data['user_prefs'] = $this->user_model->get_preference($username);
		
		$this->load->view('preference.html', $data);
	}
	
	
    // 登录时，检查用户名是否存在
    public function username_exist($str)
    {
        if (strlen($str) == 0)
        {
            $this->form_validation->set_message('username_exist', '用户名不能为空');
            return FALSE;
        }
        if(!$this->user_model->existUserName($str))
        {
            $this->form_validation->set_message('username_exist', '用户名不存在');
            return FALSE;
        }
        return TRUE;
    }
	// 登录时，检查用户名密码是否匹配
    public function validate($str)
    {
		$username = $this->temp_user;
        if(!$this->user_model->validate($username, $str))
        {
            $this->form_validation->set_message('validate', '用户名密码不匹配');
            return FALSE;
        }
        return TRUE;
    }
	
	// 注册时输入的用户名的合法性
    public function username_check($str)
    {
        if (strlen($str) == 0)
        {
            $this->form_validation->set_message('username_check', '用户名不能为空');
            return FALSE;
        }
        if (strlen($str) < 6 || strlen($str) > 20)
        {
            $this->form_validation->set_message('username_check', '用户名应该由6~20个字符组成');
            return FALSE;
        }
        if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $str)){
            $this->form_validation->set_message('username_check', '用户名应该由字母开头，并由字母、数字和下划线组成');
            return FALSE;
        }
        if($this->user_model->existUserName($str))
        {
            $this->form_validation->set_message('username_check', '该用户名已被注册');
            return FALSE;
        }
        return TRUE;
    }
	// 注册时邮箱是否已存在
    public function email_exist($str)
    {
        if($this->user_model->existEmail($str))
        {
            $this->form_validation->set_message('email_exist', '该邮箱已被注册');
            return FALSE;
        }
        return TRUE;
    }
	
	// 注册时输入的密码的合法性
    public function password_check($str)
    {
        if (strlen($str) == 0)
        {
            $this->form_validation->set_message('password_check', '密码不能为空');
            return FALSE;
        }
        if (strlen($str) < 6 || strlen($str) > 20)
        {
            $this->form_validation->set_message('password_check', '密码应该由6~20个字符组成');
            return FALSE;
        }
        return TRUE;
    }

}