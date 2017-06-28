<?php

class User extends CI_Controller
{
	private $temp_user = '';
	
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url', 'cookie'));
        $this->load->library(array('form_validation','session'));
        $this->load->model("user_model");
    }
    // index方法加载主页
    public function login()
    {
        $this->form_validation->set_rules('username', '用户名', 'callback_username_exist');
        $this->form_validation->set_rules('password', '密码', 'required',
            array('required' => '{field}不能为空'));
		
		$temp_user = $this->input->post("username");
		$data['type'] = '用户';
		$data['subtype'] = '登录';
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
	
	public function logout()
	{
		$this->session->unset_userdata("username");
		redirect(site_url('news'));
	}
	
	
	
	
	
	
}