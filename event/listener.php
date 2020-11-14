<?php
/**
*
* @package		Breizh Ajax Preview Extension
* @copyright	(c) 2019-2020 Sylver35  https://breizhcode.com
* @license		http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\ajaxpreview\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\template\template;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\config\config;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor
	 *
	 */
	public function __construct(template $template, helper $helper, language $language, config $config)
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->language = $language;
		$this->config = $config;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.posting_modify_template_vars'	=> 'posting_modify_template_vars',
			'core.ucp_pm_compose_template'		=> 'ucp_pm_compose_template',
			'core.ucp_profile_modify_signature'	=> 'ucp_profile_modify_signature',
			'core.acp_board_config_edit_add'	=> 'acp_board_config_edit_add'
		];
	}

	/**
	 * @param array $event
	 */
	public function posting_modify_template_vars($event)
	{
		$event['page_data'] = array_merge($event['page_data'], [
			'S_DISPLAY_PREVIEW'		=> true,
			'S_SHOW_AJAX_PREVIEW'	=> true,
			'S_IN_SIGNATURE'		=> false,
			'PREVIEW_DATA'			=> 'message',
			'REFRESH_TIME'			=> $this->config['ajaxpreview_refresh'] * 1000,
			'U_PREVIEW_AJAX'		=> $this->helper->route('sylver35_ajaxpreview_controller_ajax'),
		]);
	}

	/**
	 * @param array $event
	 */
	public function ucp_pm_compose_template($event)
	{
		$event['template_ary'] = array_merge($event['template_ary'], [
			'S_DISPLAY_PREVIEW'		=> true,
			'S_SHOW_AJAX_PREVIEW'	=> true,
			'S_IN_SIGNATURE'		=> false,
			'PREVIEW_DATA'			=> 'message',
			'REFRESH_TIME'			=> $this->config['ajaxpreview_refresh_pm'] * 1000,
			'U_PREVIEW_AJAX'		=> $this->helper->route('sylver35_ajaxpreview_controller_ajax'),
		]);
	}

	/**
	 * @param array $event
	 */
	public function ucp_profile_modify_signature($event)
	{
		$event['preview'] = true;
		$event['signature'] = ($event['signature'] == '') ? ' ' : $event['signature'];

		$this->template->assign_vars([
			'S_SHOW_AJAX_PREVIEW'	=> true,
			'S_IN_SIGNATURE'		=> true,
			'PREVIEW_DATA'			=> 'signature',
			'REFRESH_TIME'			=> $this->config['ajaxpreview_refresh_sign'] * 1000,
			'U_PREVIEW_AJAX'		=> $this->helper->route('sylver35_ajaxpreview_controller_ajax'),
		]);
	}

	/**
	 * @param array $event
	 */
	public function acp_board_config_edit_add($event)
	{
		$mode = $event['mode'];
		if (in_array($mode, ['post', 'message', 'signature']))
		{
			$lang = ($mode === 'signature') ? 'AJAX_PREVIEW_SIGN' : 'AJAX_PREVIEW';
			$id = [
				'post'		=> [
					'id'		=> 'ajaxpreview_refresh',
					'before'	=> 'edit_time',
				],
				'message'	=> [
					'id'		=> 'ajaxpreview_refresh_pm',
					'before'	=> 'pm_edit_time',
				],
				'signature'	=> [
					'id'		=> 'ajaxpreview_refresh_sign',
					'before'	=> 'max_sig_chars',
				],
			];

			$add_vars[$mode] = [
				$id[$mode]['id'] => [
					'lang'		=> $lang,
					'explain'	=> $lang . '_EXPLAIN',
					'validate'	=> 'int:1:99',
					'type'		=> 'number:1:99',
					'append'	=> ' ' . $this->language->lang('SECONDS'),
				],
			];

			$this->language->add_lang('ajaxpreview', 'sylver35/ajaxpreview');
			$display_vars = $event['display_vars'];
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $add_vars[$mode], ['before' => $id[$mode]['before']]);
			$event['display_vars'] = $display_vars;
		}
	}
}
