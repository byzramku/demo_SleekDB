<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

/*
	SleekDB 1.5.0
	CodeIgniter 3.1.11
	
	Implementator : Bayuaji Zaenal, S.Kom
  Created : 2020, December 03
	
	Used for API Services
*/

require_once SLEEKDB;

class Auth extends MY_Controller {
	function __contruct() {
		parent::__contruct();
	}
	
	/*
		Table Name : users
		Table Structure
		|Field Name				| All Field Type are String
		-------------------------
		|user_name				|
		|user_pass				|
		|user_klas				|
		|user_real_name			|
		|user_alamat			|
		|user_avatar			| http link
		
		Table Name : users_active
		Table Structure
		|Field Name				| All Field Type are String
		-------------------------
		|key					| from Header Token
		|user					| from Header User
		|datetime				|
		|type					| Logged In or Logged Out
	*/
	
	public function login(){
		$this->load->library('encryption');
		
		$username = $this->input->post('username', TRUE);
		$password = $this->input->post('password', TRUE);
		
		$errorMsg = '';
		
		if(trim($username) == '') { $errorMsg .= 'Username Tidak Boleh Kosong<br/>'; }
		if(trim($password) == '') { $errorMsg .= 'Password Tidak Boleh Kosong<br/>'; }
		
		if($errorMsg == ''){
			$userDB = \SleekDB\SleekDB::store('users', DATADIR);
			$findUser = $userDB
				->where('user_name', '=', $username)
				->where('user_pass', '=', md5($password))
				->fetch();
			$checkUser = count( $findUser );
			if($checkUser == 1){
				
				$encryptedKey = $this->encryption->encrypt( $username . "|=.=|" . $password );
				
				$usersActiveDB = \SleekDB\SleekDB::store('users_active', DATADIR);
				
				$newLoginData = [
					'key' => $encryptedKey,
					'user' => $username,
					'datetime' => date("Y-m-d H:i:s"),
					'type' => 'Logged In'
				];
				
				$results = $usersActiveDB->insert( $newLoginData );
				
				$results = [
					'success' => true,
					'message' => 'Berhasil Login',
					'data' => [
						'key' => $encryptedKey,
						'description' => 'Gunakan Key tersebut untuk mengakses data API lainnya',
					]
				];
				
				$this->setOutput( $results );
			}else{
				$results = [
					'success' => false,
					'message' => 'Gagal Login'
				];
				$this->setOutput( $results );
			}
		}else{
			$this->setOutput( $errorMsg );
		}
	}
	
	public function showlist(){
		$this->requireAuth();
		
		$userDB = \SleekDB\SleekDB::store('users', DATADIR);
		
		$this->setOutput( $userDB->fetch() );
	}
	
	public function showactivity(){
		$this->requireAuth();
		
		$userDB = \SleekDB\SleekDB::store('users_active', DATADIR);
		
		$username = $this->input->get_request_header('API-USER', TRUE);
		
		$this->setOutput( 
			$userDB
				->where('user', '=', $username)
				->fetch() 
		);
	}
	
	public function create(){		
		$Users = \SleekDB\SleekDB::store('users', DATADIR);
		
		$username 	= $this->input->post('username', TRUE);
		$password 	= $this->input->post('password', TRUE);
		$klass	  	= $this->input->post('klasifikasi', TRUE);
		$real_name	= $this->input->post('real_name', TRUE);
		$alamat		= $this->input->post('alamat', TRUE);
		
		$errorMsg = '';
		
		if(trim($username) == '') { $errorMsg .= 'Username Tidak Boleh Kosong<br/>'; }
		if(trim($password) == '') { $errorMsg .= 'Password Tidak Boleh Kosong<br/>'; }
		
		if($errorMsg == ''){
		
			$getUsers = $Users
				->where('user_name', '=', $username)
				->fetch();
			$UsersCount = count($getUsers);
			
			if( $UsersCount == 0 ){
			
				$newUserInsert = [
					'user_name' => $username,
					'user_pass' => md5($password),
					'user_klas' => $klass,
					'user_real_name' => $real_name,
					'user_alamat' => $alamat,
				];
				
				$results = $Users->insert( $newUserInsert );
			
				$this->setOutput( $results );
			}else{
				$results = [
					'success' => false,
					'message' => 'Username Ini Tidak Tersedia',
				];
				
				$this->setOutput( $results );
			}
		}else{
			$this->setOutput( $errorMsg );
		}
	}
	
	public function update(){
		$this->requireAuth();
		
		$Users = \SleekDB\SleekDB::store('users', DATADIR);
		
		$whereUser = $this->input->get_request_header('API-USER');
		
		$formRules = [
			[
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'required|min_length[6]|alpha_dash',
				'errors' => [
					'required' => 'Password Tidak Boleh Kosong',
					'min_length' => 'Password Minimal 6 Karakter',
					'alpha_dash' => 'Karakter diperbolehkan a-z, 0-9 dan _ '
				]
			],
			[
				'field' => 'alamat',
				'label' => 'Alamat',
				'rules' => 'required',
				'errors' => [
					'required' => 'Alamat Tidak Boleh Kosong',
				]
			],
		];
		
		$formData = $this->input->post();
		
		$this->load->library( 'form_validation' );
		
		$this->form_validation->set_data($formData);
		$this->form_validation->set_rules($formRules);
		
		if( $this->form_validation->run() == FALSE ){
			$result = [
				'success' => false,
				'message' => $this->form_validation->error_array()
			];
			
			$this->setOutput( $result );
		}else{
			$updateable = [
				'user_pass' => md5( $this->input->post('password') ),
				'user_alamat' => $this->input->post('alamat'),
				'user_avatar' => $this->input->post('avatar'),
			];
			
			$result = $Users
				->where( 'user_name', '=', $whereUser )
				->update( $updateable );
			
			$this->setOutput( $result );
		}
	}
	
	public function remove(){
		$this->requireAuth();
		
		$Users = \SleekDB\SleekDB::store('users', DATADIR);
		
		$username = $this->input->post( 'username' );
		
		$result = $Users->where( 'user_name', '=', $username )->delete();
		
		$this->setOutput( $result );
	}
}
