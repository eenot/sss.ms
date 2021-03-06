<?php
class App extends SSS
{
	
	//后台管理
	protected function admin()
	{
		//判断是否登录
		if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0) {
			//退出登录
			if (isset($_GET['do']) && $_GET['do'] == 'logout') {
				unset($_SESSION['admin_id']);
				unset($_SESSION['admin']);
				redirect('?do=login');
			}
			if (isset($_GET['do']) &&  strlen($_GET['do']) >= 4) {
				if (is_post() && $_GET['do'] == 'system') {
					foreach ($_POST as $name => $value) {
						$this->db->where("option_name", $name)->update('options', ['option_value' => $value]);
					}
					$this->success('修改成功');
				}
				include(ADMIN_TEMPLATE . "/" . $_GET['do'] . ".php");
			} else {
				include(ADMIN_TEMPLATE . "/base.php");
			}
		} else {
			if (is_post() && $_GET['do'] == 'login') {
				$username = $_POST['username'];
				$password = $_POST['password'];


				$ret = $this->db->where("username", $username)->where("password", md5(md5($password)))->getOne("admin");

				if ($ret) {
					$_SESSION['admin']['id'] = $_SESSION['admin_id'] = $ret['admin_id'];
					$_SESSION['admin']['username'] = $ret['username'];
					$_SESSION['admin']['login_time'] = $ret['login_time'];
					redirect('?do=base');
				} else {
					exit('账号密错误！');
				}
			}
			include(ADMIN_TEMPLATE . "/login.php");
		}
	}

	protected function home()
	{
		$ip = get_real_ip();
		if ($ip) {
			$intranet = $this->db->where("upload_ip", $ip)
				->where("is_lan", 1)
				->where("create_time", strtotime("-1 hours"), ">")
				->orderBy("create_time", "Desc")
				->get("file");
		}
		include(TEMPLATE . "/index.php");
	}

	protected function page()
	{
		$template_data = $this->db->where("page_url", (string)$_GET['page_url'])
			->orderBy("create_time", "Desc")
			->getOne("page");
		// p($template_data);
		include(TEMPLATE . "/page.php");
	}

	public function user()
	{
		//不登录不允许访问
		if (!isset($_SESSION['is_login']) && !$_SESSION['is_login']) {
			redirect('?a=login');
		}
		if (isset($_GET['c'])) {
			if ($_GET['c'] == 'logout') {
				//退出
				unset($_SESSION['is_login']);
				unset($_SESSION['user']);
				redirect('?a=login');
			}
			if ($_GET['c'] == 'index') {
				if (is_post()) {
					if (!isset($_POST['nickname']) || strlen($_POST['nickname']) > 12 || strlen($_POST['nickname']) < 4) {
						$this->error('用户名不符合规定，请修改');
					}
					if ($_POST['bio'] != '' && (strlen($_POST['bio']) > 24 || strlen($_POST['bio']) < 6)) {
						$this->error('个人简介不符合规定，请修改');
					}
					$nickname = (string)$_POST['nickname'];
					$bio = (string)$_POST['bio'];

					$this->db->where("user_id", $_SESSION['user']['user_id'])->update("user", ['nickname' => $nickname, 'bio' => $bio]);
					$this->success('修改成功',);
				} else {
					$template_data = $this->db->where("user_id", $_SESSION['user']['user_id'])->getOne("user");
				}
			}

			if ($_GET['c'] == 'setpass') {
				if (is_post()) {
					if (!isset($_POST['newpassword']) || strlen($_POST['newpassword']) > 24 || strlen($_POST['newpassword']) < 6) {
						$this->error('新密码不符合规定，请修改');
					}
					if (!isset($_POST['newpassword2']) || $_POST['newpassword2'] != $_POST['newpassword']) {
					}
					$user = $this->db->where("user_id", $_SESSION['user']['user_id'])->getOne("user");
					if ($user['password'] != md5(md5((string)$_POST['oldpassword']))) {
						$this->error('旧密码不正确');
					}
					$this->db->where("user_id", $user['user_id'])->update("user", ['password' => md5(md5((string)$_POST['newpassword']))]);
					$this->success('密码修改成功',);
				}
			}

			if ($_GET['c'] == 'file') {
				$template_data = $this->db->where("user_id", $_SESSION['user']['user_id'])
					->orderBy("create_time", "Desc")
					->get("file");
			}
		}
		include(TEMPLATE . "/user.php");
	}

	protected function file()
	{
		//不登录不允许访问
		if (!isset($_SESSION['is_login']) && !$_SESSION['is_login']) {
			redirect('?a=login');
		}
		if (isset($_GET['c']) && $_GET['c'] != 'index') {

			if ($_GET['c'] == 'add_folder') {
				if (is_post()) {

					//开始写入数据库
					$data = [
						"parent_id" 	=> (isset($_GET['parent_id']) && $_GET['parent_id']) ? (int)$_GET['parent_id'] : 0,
						"user_id"		=> (int)$_SESSION['user']['user_id'],
						"folder_name"	=> (string)$_POST['folder_name'],
						"access_password" => (strlen($_POST['access_password']) >= 4) ? (string)$_POST['access_password'] : null,
						"is_public" 	=> (int)$_POST['is_public'] ?: 0,
						"total_size" 	=> 0,
						"status"		=> "active",
						"create_time" 	=> time(),
						"update_time" 	=> time(),
					];
					$ret = $this->db->where("user_id", $data['user_id'])
						->where("parent_id", $data['parent_id'])
						->where("folder_name", $data['folder_name'])
						->getOne("file_folder");
					if ($ret) {
						$this->error('此文件夹已存在');
					}
					if ($data['is_public']) {
						$data['alias'] = $this->alias('file_folder');
					}

					$folder_id =  $this->db->insert('file_folder', $data);
					if ($folder_id > 0) {
						$this->success('成功', '?a=file&c=index');
					} else {
						// echo "Last executed query was ". $this->db->getLastQuery();
						$this->error('新建文件夹失败，请重试');
					}
				}
			}
			if ($_GET['c'] == 'delete') {
				if (isset($_GET['file_id']) && $_GET['file_id'] > 0) {
					$key = 'file_id';
					$id = (int)$_GET['file_id'];
					$table_name = 'file';
				} else if (isset($_GET['folder_id']) && $_GET['folder_id'] > 0) {
					$key = 'folder_id';
					$id = (int)$_GET['folder_id'];
					$table_name = 'file_folder';
				} else {
					$this->error('参数错误');
				}
				$ret = $this->db->where("user_id", $_SESSION['user']['user_id'])
					->where($key, $id)
					->getOne($table_name);
				if (!$ret) {
					$this->error('id参数错误');
				}
				$this->db->where("user_id", $_SESSION['user']['user_id'])->where($key, $id);
				if ($this->db->delete($table_name)) {
					// if(1){

					//开始删除文件
					if ($table_name == 'file') {
						if (!$this->db->where('md5', $ret['md5'])->getOne($table_name)) {
							zpl_unlink($ret['url']);
						}
					}



					$this->success('删除成功');
				} else {
					$this->error('删除失败，请稍后重试');
				}
			}
		} else {
			$parent_id = (isset($_GET['parent_id']) && $_GET['parent_id'] > 0) ? (int)$_GET['parent_id'] : 0;
			$template_data['folder'] = $this->db->where("user_id", $_SESSION['user']['user_id'])
				->where("parent_id", $parent_id)
				->orderBy("create_time", "Desc")
				->get("file_folder");

			$template_data['file'] = $this->db->where("user_id", $_SESSION['user']['user_id'])
				->where("parent_id", $parent_id)
				->orderBy("create_time", "Desc")
				->get("file");
			// p($template_data);
		}
		include(TEMPLATE . "/file.php");
	}

	protected function text()
	{
		$ip = get_real_ip();
		if ($ip) {
			$intranet = $this->db->where("upload_ip", $ip)
				->where("is_lan", 1)
				->where("create_time", strtotime("-1 hours"), ">")
				->orderBy("create_time", "Desc")
				->get("text");
		}
		include(TEMPLATE . "/text.php");
	}

	protected function encryption()
	{
		include(TEMPLATE . "/encryption.php");
	}

	protected function receive()
	{
		if (is_post()) {
			if (!isset($_POST['code'])) {
				$this->error('参数不完整');
			}
			$data = $this->db->where("alias", $_POST['code'])->getOne("file");
			if (!$data) $this->error('找不到此文件');
			if ($data['expire_time'] != NULL && $data['expire_time'] <= time()) {
				$this->error('文件失效');
			}
			redirect(url('file_share',$_POST['code']));
		}
		include(TEMPLATE . "/receive.php");
	}
	protected function login()
	{
		if (is_post()) {
			if (!isset($_POST['username']) || strlen($_POST['username']) > 12 || strlen($_POST['username']) < 4) {
				$this->error('用户名不符合规定，请修改');
			}
			if (!isset($_POST['password']) || strlen($_POST['password']) > 24 || strlen($_POST['password']) < 6) {
				$this->error('密码不符合规定，请修改');
			}
			$username = (string)$_POST['username'];
			$password = md5(md5((string)$_POST['password']));
			$ret = $this->db->where("username", $username)->where("password", $password)->getOne("user");
			if ($ret) {
				//开始执行登录操作
				$this->direct($ret['user_id']);
				redirect('?do=home');
			} else {
				$this->error('账号或密码错误');
			}
		} else {
			include(TEMPLATE . "/login.php");
		}
	}
	protected function direct($user_id)
	{
		$user = $this->db->where("user_id", $user_id)->getOne("user");
		if ($user) {
			//写入数据库
			$this->db->where("user_id", $user_id)->update("user", ['login_time' => time(), 'login_ip' => get_real_ip()]);

			$_SESSION['is_login'] = TRUE;
			$_SESSION['user'] = [
				'user_id' => $user['user_id'],
				'username' => $user['username'],
			];
		}
	}
	protected function register()
	{
		if (is_post()) {
			if (!isset($_POST['username']) || strlen($_POST['username']) > 12 || strlen($_POST['username']) < 4) {
				$this->error('用户名不符合规定，请修改');
			}
			if (!isset($_POST['password']) || strlen($_POST['password']) > 24 || strlen($_POST['password']) < 6) {
				$this->error('密码不符合规定，请修改');
			}
			if ($_POST['password'] != $_POST['password1']) {
				$this->error('两次输入的密码不一致');
			}
			//开始写入数据库
			$data = [
				"username" 		=> (string)$_POST['username'],
				"password" 		=> md5(md5((string)$_POST['password'])),
				"create_time" 	=> time(),
			];
			$ret = $this->db->where("username", $data['username'])->getOne("user");
			if ($ret) {
				$this->error('此用户名已存在');
			}
			$user_id =  $this->db->insert('user', $data);
			if ($user_id > 0) {
				$this->success('注册成功，请登录', '?do=base');
			} else {
				$this->error('注册失败，请重试');
			}
		} else {
			include(TEMPLATE . "/register.php");
		}
	}

	public function upload()
	{
		if (is_post()) {
			$is_lan = isset($_POST['isLAN']) && $_POST['isLAN'] ? 1 : 0;
			$is_only = isset($_POST['time']) && $_POST['time'] == 1 ? 1 : 0;
			$parent_id = isset($_GET['parent_id']) && $_GET['parent_id'] ? (int)$_GET['parent_id'] : 0;
			$expire_time = NULL;
			if (!$is_only && isset($_POST['time'])) {
				if ($_POST['time'] == '1d') {
					$expire_time = strtotime("+1 day");
				}
				if ($_POST['time'] == '7d') {
					$expire_time = strtotime("+7 day");
				}
			}

			if (isset($_GET['c']) && $_GET['c'] == 'text') {
				//开始写入数据库
				$data = [
					"user_id" 		=> 0,
					"alias"		=> $this->alias(),
					"content"	=> htmlentities((string)$_POST['text']),
					"is_lan"	=> $is_lan,
					"upload_ip" => get_real_ip(),
					"is_only"	=> $is_only,
					"create_time" 	=> time(),
					"expire_time" => $expire_time,
				];
				if (isset($_SESSION['is_login']) && $_SESSION['is_login']) {
					$data['user_id'] = (int)$_SESSION['user']['user_id'];
				}
				$text_id =  $this->db->insert('text', $data);
				if ($text_id > 0) {
					include(TEMPLATE . "/upload_success.php");
				} else {
					$this->error('文件上传失败，请重试');
				}
			} else {
				$md5 = md5_file($_FILES['file']['tmp_name']);
				$extension = pathinfo($_FILES['file']['name'])['extension'];
				// $save_filename = $md5 . '.' . $extension;
				// $save_filepath = 'upload/' . date('Y/m/d/', time());
				$save_filename = $md5;
				$save_filepath = 'upload/' . substr($md5, 0, 2) . '/';

				//开始创建文件目录
				zpl_mkdir($save_filepath);
				//移动临时文件到 指定目录
				$ret = move_uploaded_file($_FILES["file"]["tmp_name"], $save_filepath . $save_filename);
				if (!$ret) $this->error('文件上传失败');
				//开始写入数据库
				$data = [
					"user_id"   => 0,
					"parent_id" => $parent_id,
					"name" 		=> $_FILES["file"]["name"],
					"md5"		=> $md5,
					"alias"		=> $this->alias(),
					"suffix"	=> $extension,
					"size"	=> $_FILES["file"]["size"],
					"url"	=> $save_filepath . $save_filename,
					"is_lan"	=> $is_lan,
					"upload_ip" => get_real_ip(),
					"is_only"	=> $is_only,
					"create_time" 	=> time(),
					"expire_time" => $expire_time,
				];
				if (isset($_SESSION['is_login']) && $_SESSION['is_login']) {
					$data['user_id'] = (int)$_SESSION['user']['user_id'];
				}
				$file_id =  $this->db->insert('file', $data);
				if ($file_id > 0) {
					if (isset($_GET['c']) && $_GET['c'] == 'folder') {
						exit('ok');
					}
					include(TEMPLATE . "/upload_success.php");
				} else {
					$this->error('文件上传失败，请重试');
				}
			}
		} else {
			redirect('?do=home');
		}
	}
	public function download()
	{
		if (isset($_GET['alias']) && $_GET['alias']) {
			$alias = (string)$_GET['alias'];

			if (isset($_GET['c']) && $_GET['c'] == 'text') {
				$data = $this->db->where("alias", $alias)->getOne("text");
				//给阅后即焚文本进行设置到期时间
				if ($data['is_only'] == 1) {
					// exit('设置到期时间');
					$this->db->where("alias", $alias)->update("text", ['expire_time' => time()]);
				}
			} else {
				$data = $this->db->where("alias", $alias)->getOne("file");
			}

			if (!$data) $this->error('找不到此文件');
			if ($data['expire_time'] != NULL && $data['expire_time'] <= time()) {
				$this->error('文件失效');
			}

			if (isset($_GET['c']) && $_GET['c'] == 'download') {
				$file_dir = ROOT . '/' . $data['url'];
				//检查文件是否存在 
				if (!file_exists($file_dir)) {
					$this->error('文件找不到');
				} else {
					$file = fopen($file_dir, "r"); // 打开文件  
					// 输入文件标签  
					Header("Content-type: application/octet-stream");
					Header("Accept-Ranges: bytes");
					Header("Accept-Length: " . filesize($file_dir));
					Header("Content-Disposition: attachment; filename=" . $data['name']);
					// 输出文件内容  
					echo fread($file, filesize($file_dir));
					fclose($file);
				}
				//给阅后即焚文件 进行设置到期时间
				if ($data['is_only'] == 1) {
					// exit('设置到期时间');
					$this->db->where("file_id", $data['file_id'])->update("file", ['expire_time' => time()]);
				}
				return;
			}
		}
		include(TEMPLATE . "/download.php");
	}

	protected function alias($table_name = 'file')
	{
		$unique = FALSE;
		$max_loop = 100;
		$i = 0;
		$alias_length = $this->config['alias_length'];

		while (!$unique) {
			// retry if max attempt reached
			if ($i >= $max_loop) {
				$alias_length++;
				$i = 0;
			}
			$alias = strrand($this->config['alias_string'], $alias_length);
			if (!$this->db->where("alias", $alias)->getOne($table_name)) $unique = TRUE;
			$i++;
		}
		return $alias;
	}


	
}
