<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	SleekDB 1.5.0
	CodeIgniter 3.1.11
	
	Implementator : Bayuaji Zaenal, S.Kom
	
	Used for API Services
*/

class MY_Controller extends CI_Controller {
	
	public function __contruct(){
		parent::__contruct();
	}
	
	public function requireAuth(){ 
		/* Get Headers Required Information */
		$api_user 	= $this->input->get_request_header('API-USER', TRUE);
		$api_token	= $this->input->get_request_header('API-TOKEN', TRUE);
		
		$errMsg = '';
		
		/* Check Headers Variable */
		if( empty( $api_user ) || $api_user == null || trim( $api_user == '' ) ){ $errMsg .= 'API User Kosong '; }
		if( empty( $api_token ) || $api_token == null || trim( $api_token == '' ) ){ $errMsg .= 'API Token Kosong '; }
		
		/* If Not Validate, Then Sent Output, Exit */
		if( $errMsg != '' ){
			$result = [
				'success' => false,
				'message' => 'Kesalahan: ' . $errMsg,
				'headers' => [
					'api_user' => $api_user,
					'api_token' => $api_token,
				]
			];
			
			$this->setOutput( $result );
			exit;
		}else{
			
			$userActivityDB = \SleekDB\SleekDB::store('users_active', DATADIR);
			
			$getData = $userActivityDB
				->where('user', '=', $api_user)
				->where('key', '=', $api_token)
				->fetch();
				
			$isAvailable = count( $getData );
			
			/* If Not Available, Then Sent Output, Exit */
			if( $isAvailable == 0 ){
				
				$result = [
					'success' => false,
					'message' => 'Kesalahan: User Tidak Aktif',
				];
				
				$this->setOutput( $result );
				exit;
			}
			
		}
	}
	
	public function setOutput( $data ){
		header("Access-Control-Allow-Origin: *");
		header("Content-type: application/json; charset=utf-8");
		
		echo json_encode( $data );
	}
	
}
