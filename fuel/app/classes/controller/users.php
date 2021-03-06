<?php

class Controller_Users extends Controller_Template {

    public function action_index() {
        if (Auth::has_access('chat.read')){
            Response::redirect('chat/index');
        }
        $view_login = View::forge('users/login');
        $form_login = Fieldset::forge('login');
        $form_login->add('username', 'Username:');
        $form_login->add('password', 'Choose Password:', array('type' => 'password'));
        $form_login->add('submit', ' ', array('type' => 'submit', 'value' => 'Login'));
           
        $view_reg = View::forge('users/register');
        $form_reg = Fieldset::forge('register');
        Model_Orm_User::register($form_reg);
        
        $auth = Auth::instance();
        if ($_POST) {
            $form_reg->repopulate();
            $form_login->repopulate();
            $result = Model_Orm_User::validate_registration($form_reg, $auth);
            if ($auth->login(htmlentities(Input::post('username')), Input::post('password'))) {
                $new = new Model_Orm_Active();
                $name = $auth->get_screen_name();
                $new->name = htmlentities($name);
                $group = Model_Orm_User::get_group($name);
                $new->group = $group;
                $new->save();
                
                Session::set_flash('success', 'Successfully logged in! Welcome ' . $auth->get_screen_name());
                Response::redirect('chat/');
            }else if (isset($result['e_found'])) {
                $view_reg = View::forge('users/register');
                $view_login = View::forge('users/login');
                Session::set_flash('error', 'Username or password incorrect! Or you can register and become one of us for FREE!');
                $view_reg->set('errors', $result['errors'], false);
            }
        }
        
        
        $view_login->set('form', $form_login, false);
        $this->template->login = $view_login;
        
        $view_reg->set('reg', $form_reg->build(), false);
        $this->template->register = $view_reg; //View::forge('users/register');
        
        $this->template->col_2 = "set";
        $this->template->title = 'User Authentification!';
    }

    public function action_logout() {
        $auth = Auth::instance();
        $name = $auth->get_screen_name();

        Model_Orm_Active::remove_active($name);
        
        $auth->logout();
        //$auth->guest_login();
        
        Session::set_flash('success', 'Logged out.');
        Response::redirect('users/index');
    }
}