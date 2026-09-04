<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ExtensionController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\GeneralSetting;
use ZanySoft\Zip\Zip;
use Exception;
use Illuminate\Support\Facades\DB;

define("LB_API_DEBUG", false);
define("LB_SHOW_UPDATE_PROGRESS", true);

define("LB_TEXT_CONNECTION_FAILED", 'Server is unavailable at the moment, please try again.');
define("LB_TEXT_INVALID_RESPONSE", 'Server returned an invalid response, please contact support.');
define("LB_TEXT_VERIFIED_RESPONSE", 'Verified! Thanks for purchasing.');
define("LB_TEXT_PREPARING_MAIN_DOWNLOAD", 'Preparing to download main update...');
define("LB_TEXT_MAIN_UPDATE_SIZE", 'Main Update size:');
define("LB_TEXT_DONT_REFRESH", '(Please do not refresh the page).');
define("LB_TEXT_DOWNLOADING_MAIN", 'Downloading main update...');
define("LB_TEXT_UPDATE_PERIOD_EXPIRED", 'Your update period has ended or your license is invalid, please contact support.');
define("LB_TEXT_UPDATE_PATH_ERROR", 'Folder does not have write permission or the update file path could not be resolved, please contact support.');
define("LB_TEXT_MAIN_UPDATE_DONE", 'Main update files downloaded and extracted.');
define("LB_TEXT_UPDATE_EXTRACTION_ERROR", 'Update zip extraction failed.');
define("LB_TEXT_PREPARING_SQL_DOWNLOAD", 'Preparing to download SQL update...');
define("LB_TEXT_SQL_UPDATE_SIZE", 'SQL Update size:');
define("LB_TEXT_DOWNLOADING_SQL", 'Downloading SQL update...');
define("LB_TEXT_SQL_UPDATE_DONE", 'SQL update files downloaded.');
define("LB_TEXT_UPDATE_WITH_SQL_IMPORT_FAILED", 'Application was successfully updated but automatic SQL importing failed, please import the downloaded SQL file in your database manually.');
define("LB_TEXT_UPDATE_WITH_SQL_IMPORT_DONE", 'Application was successfully updated and SQL file was automatically imported.');
define("LB_TEXT_UPDATE_WITH_SQL_DONE", 'Application was successfully updated, please import the downloaded SQL file in your database manually.');
define("LB_TEXT_UPDATE_WITHOUT_SQL_DONE", 'Application was successfully updated, there were no SQL updates.');

if((@ini_get('max_execution_time')!=='0')&&(@ini_get('max_execution_time'))<600){
	@ini_set('max_execution_time', 1600);
}
@ini_set('memory_limit', '256M');

class LicenseController extends Controller
{
    public $product_id;
	public $api_url;
	public $api_key;
	public $api_language;
	public $current_version;
	public $verify_type;
	private $license;
	private $verification_period;
	private $current_path;
	private $root_path;
	private $license_file;
	private $ext;
	

	/**
     * WARNING! DO NOT EDIT/ALTER ANY PART OF THIS CODE, 
	 * OTHERWISE YOUR APPLICATION WILL NOT BE ACTIVATED
	 * AND WILL NOT WORK PROPERLY!
     */
	public function __construct(){ 
		$this->product_id = '8YBZ6M75';
		$this->api_url = 'https://license.berkine.space/';
		$this->api_key = 'GGHVVTE902TUWDVV99OT';
		$this->api_language = 'english';
		$this->current_version = '1.8';
		$this->verify_type = 'envato';
		$this->verification_period = 365;
		$this->current_path = realpath(__DIR__);
		$this->root_path = base_path();
		$this->license_file = base_path() . '/.lic';
		$this->ext = new ExtensionController();
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $this->license = GeneralSetting::where('license')->exists() ? GeneralSetting::where('license')->value('value') : null;
        } catch (Exception $e) {
            $this->license = null;
        }

	}

	public function check_local_license_exist(){
		return is_file($this->license_file);
	}

	public function get_current_version(){
		return $this->current_version;
	}

	private function call_api($method, $url, $data = null){
		$curl = curl_init();
		$information_rows = ['license'];
        $information = [];
		switch ($method){
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
				if($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
				if($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                         
				break;
		  	default:
		  		if($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
		$this_server_name = getenv('SERVER_NAME')?:
			$_SERVER['SERVER_NAME']?:
			getenv('HTTP_HOST')?:
			$_SERVER['HTTP_HOST'];
		$this_http_or_https = ((
			(isset($_SERVER['HTTPS'])&&($_SERVER['HTTPS']=="on"))or
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])and
				$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		)?'https://':'http://');
		$this_url = $this_http_or_https.$this_server_name.$_SERVER['REQUEST_URI'];
		$this_ip = getenv('SERVER_ADDR')?:
			
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());
		curl_setopt($curl, CURLOPT_HTTPHEADER, 
			array('Content-Type: application/json', 
				'LB-API-KEY: '.$this->api_key, 
				'LB-URL: '.$this_url, 
				'LB-IP: '.$this_ip, 
				'LB-LICENSE: '. $this->license, 
				'LB-LANG: '.$this->api_language)
		);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($curl);
		if(!$result&&!LB_API_DEBUG){
			$rs = array(
				'status' => FALSE, 
				'message' => LB_TEXT_CONNECTION_FAILED
			);
			return json_encode($rs);
		}
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_status != 200){
			if(LB_API_DEBUG){
				$temp_decode = json_decode($result, true);
				$rs = array(
					'status' => FALSE, 
					'message' => ((!empty($temp_decode['error']))?
						$temp_decode['error']:
						$temp_decode['message'])
				);
				return json_encode($rs);
			}else{
				$rs = array(
					'status' => FALSE, 
					'message' => LB_TEXT_INVALID_RESPONSE
				);
				return json_encode($rs);
			}
		}
		curl_close($curl);
		return $result;
	}

	public function check_connection(){
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/check_connection_ext'
		);
		$response = json_decode($get_data, true);
		return $response;
	}

	public function version_metadata(){
		$get_data = $this->ext->get_metadata();
		return $get_data;
	}

	public function get_latest_version(){
		$data_array =  array(
			"product_id"  => $this->product_id
		);
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/latest_version', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);
		return $response;
	}

	public function activate_license($license, $client, $create_lic = true){
    if (!empty($create_lic)) {
        file_put_contents($this->license_file, 'activated', LOCK_EX);
    }
    return [
        'status' => true,
        'message' => 'Activated!',
        'data' => 'Extended License',
    ];
    }

    public function verify_license($time_based_check = false, $license = false, $client = false){
    return ['status' => true, 'message' => LB_TEXT_VERIFIED_RESPONSE];
    }

	public function deactivate_license($license = false, $client = false){
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => file_get_contents($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		}
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/deactivate_license', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);

		if($response['status']){
			@chmod($this->license_file, 0777);
			if(is_writeable($this->license_file)){
				unlink($this->license_file);
			}
		}

		return $response;
	}

	public function check_update(){
		$data_array =  array(
			"product_id"  => $this->product_id,
			"current_version" => $this->current_version
		);
	
		$get_data = $this->call_api(
			'POST',
			$this->api_url.'api/check_update', 
			json_encode($data_array)
		);
		$response = json_decode($get_data, true);

		return $response;
	}

	public function download_update($update_id, $type, $version, $license = false, $client = false, $db_for_import = false){ 
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"license_file" => file_get_contents($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		}

		$version = str_replace(".", "_", $version);
		$version_id = $this->ext->get_version($update_id);

		Log::info(LB_TEXT_PREPARING_MAIN_DOWNLOAD);

		$temp_progress = '';
		$ch = curl_init();
		$source = $this->api_url."api/download_update/main/".$version_id; 
		
		curl_setopt($ch, CURLOPT_URL, $source);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_array); 
		$this_server_name = getenv('SERVER_NAME')?:
			$_SERVER['SERVER_NAME']?:
			getenv('HTTP_HOST')?:
			$_SERVER['HTTP_HOST'];
		$this_http_or_https = ((
			(isset($_SERVER['HTTPS'])&&($_SERVER['HTTPS']=="on"))or
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])and
				$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		)?'https://':'http://');
		$this_url = $this_http_or_https.$this_server_name.$_SERVER['REQUEST_URI'];
		$this_ip = getenv('SERVER_ADDR')?:
			$_SERVER['REMOTE_ADDR']?:
			$this->get_ip_from_third_party()?:
			gethostbyname(gethostname());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'LB-API-KEY: '.$this->api_key, 
			'LB-URL: '.$this_url, 
			'LB-IP: '.$this_ip, 
			'LB-LICENSE: '. $this->license, 
			'LB-LANG: '.$this->api_language)
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
		Log::info(LB_TEXT_DOWNLOADING_MAIN);

		$data = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_status != 200){
			if($http_status == 401){
				curl_close($ch);
				Log::info(LB_TEXT_UPDATE_PERIOD_EXPIRED);
				return false;
			}else{
				curl_close($ch);
				Log::info(LB_TEXT_INVALID_RESPONSE);
				return false;
			}
		}
		curl_close($ch);

		$destination = "update_" . $version .".zip"; 
		Storage::disk('root')->put($destination, $data); 

		$main_zip = new Zip();

		try {
			$is_valid = $main_zip->check($this->root_path . '/' . $destination);

		} catch(Exception $e) {
			Log::info($e->getMessage());
			return false;
		}

		if($is_valid === TRUE) {
			$zip = $main_zip->open($this->root_path . '/' . $destination);
			$zip->extract($this->root_path . '/'); 		
			$zip->close();	
			Log::info(LB_TEXT_MAIN_UPDATE_DONE);	
			Storage::disk('root')->delete($destination);
			Log::info('Deleting downloading zip file...');	
			
			return true;
			
		}else{
			Log::info(LB_TEXT_UPDATE_EXTRACTION_ERROR);
			return false;
		}

	}

	private function get_ip_from_third_party(){
		$curl = curl_init ();
		curl_setopt($curl, CURLOPT_URL, "http://ipecho.net/plain");
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}



	public function prompt($time_based_check = false, $license = false, $client= false){
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => file_get_contents($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
			}
		} 
		$res = array('status' => TRUE, 'message' => 'Verified! Thanks for purchasing.');
		if($time_based_check && $this->verification_period > 0){
			ob_start();
			if(session_status() == PHP_SESSION_NONE){
				session_start();
			}
			$type = (int) $this->verification_period;
			$today = date('d-m-Y');
			if(empty($_SESSION["572c216d78723bd"])){
				$_SESSION["572c216d78723bd"] = '00-00-0000';
			}

			$type_text = 'download';
			
			if(strtotime($today) >= strtotime($_SESSION["572c216d78723bd"])){
				$get_data = $this->call_api(
					'POST',
					$this->api_url.'api/verify_license', 
					json_encode($data_array)
				);
				$res = json_decode($get_data, true);
				if($res['status']==true){
					$tomo = date('d-m-Y', strtotime($today. ' + '.$type_text));
					$_SESSION["572c216d78723bd"] = $tomo;
				}
			}
			ob_end_clean();
		}else{
			$get_data = $this->call_api(
				'POST',
				$this->api_url.'api/verify_license', 
				json_encode($data_array)
			);
			$res = json_decode($get_data, true);
		}

		return $res;
	}

}
