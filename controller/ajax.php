<?php
/**
*
* @package		Breizh Ajax Preview Extension
* @copyright	(c) 2019-2020 Sylver35  https://breizhcode.com
* @license		http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace sylver35\ajaxpreview\controller;
use phpbb\request\request;

class ajax
{
	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 */
	public function __construct(request $request)
	{
		$this->request = $request;
	}

	/**
	 * Function construct_ajax
	 *
	 * @return void
	 */
	public function construct_ajax()
	{
		$message = $this->request->variable('content', '', true);
		$json_response = new \phpbb\json_response;
		$uid = $bitfield = $options = '';

		generate_text_for_storage($message, $uid, $bitfield, $options, true, false, true);
		$message = generate_text_for_display($message, $uid, $bitfield, $options);
		// Always display images/smilies with correct url
		$message = str_replace(['src="./../../', 'src="./../', 'src="./'], 'src="' . generate_board_url() . '/', $message);
		// A litle magic for simple mentions
		$message = str_replace(['[mention]', '[/mention]'], ['<span class="mention">', '</span>'], $message);

		$json_response->send([
			'content'	=> $message,
		], true);
	}
}
