<?php
/**
*
* @package Board3 Portal Birthday Announce Module
* @copyright (c) 2015 Theriddler ( http://www.phpbbservice.nl )
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace theriddler\b3p_birthday_announce;

/**
* @package Birthday Announce Module
*/
class birthday_announce extends \board3\portal\modules\module_base
{
	/**
	* Allowed columns: Just sum up your options (Exp: left + right = 10)
	* top		1
	* left		2
	* center	4
	* right		8
	* bottom	16
	*/
	public $columns = 21;

	/**
	* Default modulename
	*/
	public $name = 'BIRTHDAY_ANNOUNCE';

	/**
	* Default module-image:
	* file must be in "{T_THEME_PATH}/images/portal/"
	*/
	public $image_src = '';

	/**
	* module-language file
	* file must be in "language/{$user->lang}/portal/"
	*/
	public $language = array(
		'vendor'	=> 'theriddler/b3p_birthday_announce',
		'file'		=> 'birthday_announce',
	);

	
	protected $config, $template, $db, $user, $root_path, $ext_manager;

	public function __construct($config, $db, $template, $user, $ext_manager, $root_path)
	{
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->ext_manager = $ext_manager;
	}
	
	
	public function is_enabled_ext($ext_name)
	{
		return $this->ext_manager->is_available($ext_name) && $this->ext_manager->is_enabled($ext_name);
	}

	public function ext_web_path($ext_name = 'theriddler/b3p_birthday_announce')
	{
		return $this->web_path() . $this->ext_path();
	}

	public function ext_path($ext_name = 'theriddler/b3p_birthday_announce', $phpbb_relative = false)
	{
		return $this->ext_manager->get_extension_path($ext_name, $phpbb_relative);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_template_center($module_id)
	{
		if ($this->config['load_birthdays'] && $this->config['allow_birthdays'])
		{
		$announcement_birthday_list = $announcement_birthday_img = '';

		// Generate birthday list if required ...
		$time = $this->user->create_datetime();
		$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
			FROM ' . USERS_TABLE . ' u
			LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
			WHERE (b.ban_id IS NULL
				OR b.ban_exclude = 1)
				AND u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%'
				AND " . $this->db->sql_in_set('u.user_type', array(USER_NORMAL, USER_FOUNDER));
		$result = $this->db->sql_query($sql, 3600);

		while ($user_row = $this->db->sql_fetchrow($result))
		{
			
			//obtain the avatar and username for the birthday announcements

			$announcement_birthday_list .= (($announcement_birthday_list != '') ? ', ' : '') . get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);
			
			$avatar_data = array(
					'user_avatar'			=> $user_row['user_avatar'],
					'user_avatar_type'		=> $user_row['user_avatar_type'],
					'user_avatar_width'		=> 100,
					'user_avatar_height'	=> 100
				);
			
			$avatar = phpbb_get_user_avatar($avatar_data);
			$root_path_ext = $this->ext_path('theriddler/b3p_birthday_announce', true);
			
			if (!$avatar)
				{
					$avatar = '<img class="avatar" src="' . $root_path_ext . 'images/no_avatar.gif" width="100" height="100" alt="" />';
				}

			$this->template->assign_block_vars('bdannounce', array(
			'BIRTHDAY_ANNOUNCEMENT_AVATAR'		=> $avatar,
			'BIRTHDAY_ANNOUNCEMENT_USERNAME'	=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour'])));

		}
		$this->db->sql_freeresult($result);
	}
	$announcement_birthday_img = '<img src="' . $root_path_ext . 'images/birthday.png" title="' . $this->user->lang['CONGRATULATIONS'] . '" alt="' . $this->user->lang['CONGRATULATIONS'] . '" />';
	
		$this->template->assign_vars(array(
		'BIRTHDAY_ANNOUNCEMENT' 			=> $announcement_birthday_list,
		'BIRTHDAY_ANNOUNCEMENT_IMG'			=> $announcement_birthday_img,
		'BIRTHDAY_ANNOUNCEMENT_MESSAGE'		=> $this->user->lang('BIRTHDAY_ANNOUNCEMENT_TEXT', $this->config['sitename']),
		));

		return '@theriddler_b3p_birthday_announce/birthday_announce_center.html';
	}

		/**
	* {@inheritdoc}
	*/
	public function get_template_acp($module_id)
	{
		return array(
			'title'	=> 'BIRTHDAY_ANNOUNCE',
			'vars'	=> array(
		),
	);
	}

	/**
	* {@inheritdoc}
	*/
	public function install($module_id)
	{
		$this->config->delete('theriddler_b3p_birthday_announce' . $module_id);
		$this->config->delete('theriddler_b3p_birthday_announce' . $module_id);
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function uninstall($module_id, $db)
	{
		$this->config->delete('theriddler_b3p_birthday_announce' . $module_id);
		$this->config->delete('theriddler_b3p_birthday_announce' . $module_id);
		return true;
	}
}