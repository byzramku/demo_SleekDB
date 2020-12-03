# demo_SleekDB
Implementasi noSQL SleekDB versi 1.5.0 dengan CodeIgniter 3.1.11

Content Author : Bayuaji Zaenal, S.Kom

Publish Date : 2020, December 03

### Sample CRUD API

1. Create ( Insert )
    * $sleekDB->insert()
2. Fetch List
    * $sleekDB->fetch()
    * $sleekDB->Where() => Single & Multi Filter
3. Update
    * $sleekDB->Update()
    * $sleekDB->Where() => Filter Implementation inside Update
4. Remove ( Delete )
    * $sleekDB->Delete()
    * $sleekDB->Where() => Filter Implementation inside Delete

> Example Create

```php
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
```

> Example Fetch

```php
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
```

> Example Update

```php
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
```

> Example Delete

```php
	public function remove(){
		$this->requireAuth();
		
		$Users = \SleekDB\SleekDB::store('users', DATADIR);
		
		$username = $this->input->post( 'username' );
		
		$result = $Users->where( 'user_name', '=', $username )->delete();
		
		$this->setOutput( $result );
	}
```
