<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscription extends Home_Controller {

	public function __construct()
    {
        parent::__construct();

        if (!is_user()) {
            redirect(base_url());
        }
    }

    public function index()
    {
        $data = array();
        $this->upgrade_plans();
        $data['page_title'] = 'Subscription';
        $data['user'] = $this->common_model->get_my_package();
        $data['packages'] = $this->admin_model->get_active_packages('package');
        $data['features'] = $this->admin_model->select_asc('package_features');
        if(user()->user_type == 'trial'){$page_load = 'trial_subscription';}else{$page_load = 'subscription';}
        $data['main_content'] = $this->load->view('admin/user/'.$page_load, $data, TRUE);
        $this->load->view('admin/index', $data);
    }


    public function upgrade($slug='', $billing_type='', $status=0)
    {
        if ($status == 0) {
            $data = array();
            $data['slug'] = $slug;      
            $data['billing_type'] = $billing_type;

            $data['main_content'] = $this->load->view('admin/user/payment_confirm',$data,TRUE);
            $this->load->view('admin/index',$data);
        } else {
            

            $data = array();
            $data['page_title'] = 'Upgrade';      
            $data['page'] = 'Payment'; 
            $payment = $this->common_model->get_user_payment();
            $uid = random_string('numeric',5);
            $data['payment_id'] = (user()->user_type == 'trial' ? $uid : $payment->puid);
            $data['billing_type'] = $billing_type;
            $data['package'] = $this->common_model->get_package_by_slug($slug);
            $payments = $this->admin_model->get_previous_payments(user()->id);
            $package = $data['package'];
            $uid = random_string('numeric',5);

            
            
            if($billing_type =='monthly'):
                $amount = $package->monthly_price;
                $expire_on = date('Y-m-d', strtotime('+1 month'));
            else:
                $amount = $package->price;
                $expire_on = date('Y-m-d', strtotime('+12 month'));
            endif;

            if (number_format($amount, 0) == 0):
                $status = 'verified';
            else:
                $status = 'pending';
            endif;


            //create payment
            $pay_data=array(
                'user_id' => user()->id,
                'puid' => $uid,
                'package' => $package->id,
                'amount' => $amount,
                'billing_type' => $billing_type,
                'status' => $status,
                'created_at' => my_date_now(),
                'expire_on' => $expire_on
            );
            $pay_data = $this->security->xss_clean($pay_data);

            if ($this->settings->enable_paypal == 0 || number_format($amount, 0) == 0) {
                foreach ($payments as $pay) {
                    $pays_data=array(
                        'status' => 'expired'
                    );
                    $this->common_model->edit_option($pays_data, $pay->id, 'payment');
                }

                $this->common_model->insert($pay_data, 'payment');

                if (user()->user_type == 'trial') {
                    //update user type
                    $user_data=array(
                        'user_type' => 'registered',
                        'trial_expire' => date('Y-m-d')
                    );
                    $this->common_model->edit_option($user_data, user()->id, 'users');
                }
            }
            
            if (number_format($amount, 0) == 0){
                redirect(base_url('admin/subscription'));
            }else{
                if ($this->settings->enable_paypal == 1) {
                    $data['main_content'] = $this->load->view('admin/user/payment',$data,TRUE);
                    $this->load->view('admin/index',$data);
                } else {
                    redirect(base_url('admin/subscription'));
                }
            }
        }
        
    }


    //payment success
    public function payment_success($billing_type, $package_id, $payment_id)
    {   

        $payments = $this->admin_model->get_previous_payments(user()->id);
        foreach ($payments as $pay) {
            $pays_data=array(
                'status' => 'expired'
            );
            $this->common_model->edit_option($pays_data, $pay->id, 'payment');
        }
        

        $package = $this->common_model->get_package_by_id($package_id);
        $payment = $this->common_model->get_payment($payment_id);
        $uid = random_string('numeric',5);
        
        if($billing_type =='monthly'):
            $amount = $package->monthly_price;
            $expire_on = date('Y-m-d', strtotime('+1 month'));
        else:
            $amount = $package->price;
            $expire_on = date('Y-m-d', strtotime('+12 month'));
        endif;

        $data = array();
        $pay_data = array(
            'user_id' => user()->id,
            'package' => $package->id,
            'puid' => $payment_id,
            'status' => 'verified',
            'billing_type' => $billing_type,
            'amount' => $amount,
            'expire_on' => $expire_on,
            'created_at' => my_date_now()
        );
        $pay_data = $this->security->xss_clean($pay_data);
        $this->common_model->insert($pay_data, 'payment');

        if (user()->user_type == 'trial') {
            //update user type
            $user_data=array(
                'user_type' => 'registered',
                'trial_expire' => '0000-00-00'
            );
            $this->common_model->edit_option($user_data, user()->id, 'users');
        }

        //affiliate code
        $referral_settings = $this->admin_model->get_referral_settings();

        if ($referral_settings->is_enable == 1) {
            $register_user = $this->admin_model->get_by_referral_user(user()->id);
   
            $commision = $referral_settings->commision_rate;
            $commision_amount = ($commision * $amount) / 100; 

            $ref_data=array(
                'status' => 1,
                'amount' => $amount,
                'commision' => $commision,
                'commision_amount' => $commision_amount
            );
            $this->admin_model->edit_option($ref_data, $register_user->id, 'referrals');



            $user = $this->admin_model->get_by_referral_id($register_user->referrar_id);
            
            if (!empty($register_user)) {
                $user_id = $user->id ;
                $ref_earn = $user->referral_earn;
                $update_balance = $ref_earn + $register_user->commision_amount ;

                $earn_data = array(
                    'referral_earn' => $update_balance,
                );

                $earn_data = $this->security->xss_clean($earn_data);
                $this->admin_model->edit_option($earn_data, $user_id, 'users');
            }
        }
        //affiliate code


      
        $data['success_msg'] = 'Success';
        $data['main_content'] = $this->load->view('admin/user/payment_msg',$data,TRUE);
        $this->load->view('admin/index',$data);

    }


    public function upgrade_plans()
    {
        if (!empty(settings()->sid) && settings()->sid == '2020-02-02') {
            return true;
        }else{
            if (settings()->sid < '2021-12-14') {
                return true;
            }else{ 
                if (settings()->site_info == 2){
                    return true;
                }else{
                    $user_data=array(
                        'enable_paypal' => '0'
                    );
                    $this->common_model->edit_option($user_data, 1, 'settings');
                }
            }
        }
    }


    //payment cancel
    public function payment_cancel($billing_type, $package_id, $payment_id)
    {   
        $data = array();
        $data['error_msg'] = 'Error';
        $data['main_content'] = $this->load->view('admin/user/payment_msg',$data,TRUE);
        $this->load->view('admin/index',$data);
    }


  


}