<?php
	/**
	 * User: shifu204
	 * Date: 2017/6/13
	 */

	namespace think\session\driver;

	use SessionHandler;
	use think\Session;

// -- ----------------------------
// -- Table structure for platform_session
// -- session DB驱动表
// -- ----------------------------
// DROP TABLE IF EXISTS `platform_session`;
// CREATE TABLE `platform_session` (
//   `session_id` char(40) NOT NULL COMMENT 'SESSION键',
//   `mem_id` INT(11) NOT NULL COMMENT '登录会员id',
//   `data` blob,#必须用blob属性
//   `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'SESSION更新时间',
//   PRIMARY KEY (`session_id`),
//   UNIQUE KEY `session_id` (`session_id`)
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	class Db extends SessionHandler
	{
		protected $handler = null;
		protected $config  = [
			'expire'     => 0, // Session有效期
			'prefix'     => 'think', // Session前缀
			'table_name' => 'session', // 表名（不包含前缀）
			'db_config'  => '', //应用配置文件中配置的额外的数据库连接信息
		];

		public function __construct($config = [])
		{
			$this->config = array_merge($this->config, $config);
		}

		/**
		 * 打开Session
		 * @access public
		 * @param string $save_path
		 * @param mixed $session_name
		 * @return bool
		 */
		public function open($save_path, $session_name)
		{
			if ($this->config['db_config'] === '') {
				$this->handler = \think\Db::name($this->config['table_name']);
			} else {
				$this->handler = \think\Db::connect($this->config['db_config'])->name($this->config['table_name']);
			}

			return true;
		}

		/**
		 * 关闭Session
		 * @access public
		 */
		public function close()
		{
			$this->gc(ini_get('session.gc_maxlifetime'));
			$this->handler = null;
			return true;
		}

		/**
		 * 读取Session
		 * @access public
		 * @param string $session_id
		 * @return bool|string
		 */
		public function read($session_id)
		{
			$map['session_id'] = ['eq', $this->config['prefix'] . $session_id];
			if ($this->config['expire'] != 0) {
				$map['update_time'] = ['gt', time() - $this->config['expire']];
			}
			return $this->handler->where($map)->value('data');
		}

		/**
		 * 写入Session
		 * @access public
		 * @param string $session_id
		 * @param String $session_data
		 * @return bool
		 */
		public function write($session_id, $session_data)
		{
        	$mem_id = Session::get('admin')? Session::get('admin') : 0 ;//在session中获取$mem_id

			$result = $this->handler->where('session_id', $this->config['prefix'] . $session_id)->find();
			$data   = ['session_id' => $this->config['prefix'] . $session_id, 'update_time' => time(), 'data' => $session_data, 'mem_id' => $mem_id];/*, 'mem_id' => $mem_id*/
			if ($result) {
				$affect_rows = $this->handler->update($data);
			} else {
				$affect_rows = $this->handler->insert($data);
			}

			return $affect_rows ? true : false;
		}

		/**
		 * 删除Session
		 * @access public
		 * @param string $session_id
		 * @return bool
		 */
		public function destroy($session_id)
		{
			$result = $this->handler->where('session_id', $this->config['prefix'] . $session_id)->delete();
			return $result ? true : false;
		}

		/**
		 * Session 垃圾回收
		 * @access public
		 * @param string $sessMaxLifeTime
		 * @return bool
		 */
		public function gc($sessMaxLifeTime)
		{
			if ($this->config['expire'] != 0) {
				$map['update_time'] = ['lt', time() - $this->config['expire']];
			} else {
				$map['update_time'] = ['lt', time() - $sessMaxLifeTime];
			}

			$result = $this->handler->where($map)->delete();
			return $result ? true : false;
		}

	}
