<?php
// ALTER TABLE `cc_messages`
//   DROP `orderId`,
//   DROP `isFromDelete`,
//   DROP `isToDelete`,
//   DROP `en_subject`,
//   DROP `replyTo`,
//   DROP `replyToSubject`,
//   DROP `replyToUser`,
//   DROP `related_message_id`,
//   DROP `related_coupon_id`,
//   DROP `autoOrmanualSend`,
//   DROP `files`,
//   DROP `msg_type`;

class mdl_messages extends mdl_base
{
	protected $tableName = '#@_messages';
	
	private $to;
	private $from;
    private $subject;
    private $content;

    private $sendTime;
    private $isRead;

    /**
     * UserId
     * @param  [int] $from [description]
     * @return [type]       [description]
     */
    public function from($from)
	{
		$this->from = $from;
		return $this;
	}

	/**
	 * UserId
	 * @param  [int] $to [description]
	 * @return [type]     [description]
	 */
	public function to($to){
		$this->to = $to;
		return $this;
	}

	/**
	 * subject 
	 * @param  [string] $subject [description]
	 * @return [type]          [description]
	 */
	public function subject($subject){
		$this->subject = $subject;
		return $this;
	}

	/**
	 * content 
	 * @param  [string] $content [description]
	 * @return [type]          [description]
	 */
	public function content($content)
	{
		$this->content = $content;
		return $this;
	}

	//Action send
	public function send()
	{
		$data =array(
			'from'=>$this->from,
			'to'=>$this->to,
			'subject'=>$this->subject,
			'content'=>$this->content,
			'sendTime'=>time(),
		);

		return $this->insert($data);
	}

}

?>