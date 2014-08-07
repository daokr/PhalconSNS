<?php
/**
 * Author : 小麦
 * 通用发送Email类 
 */
namespace IKPHP\Org;

use Phalcon\Mvc\User\Component;
use IKPHP\Vendor\Mail\PHPMailer as Mailer;

class Mail extends Component{
	private $conf = null;
	//构造函数
	public function __construct($conf){
		$this->conf = $conf->toArray();
	}
	//发送邮件
	function postMail($sendmail,$subject,$content){
		date_default_timezone_set('Asia/Shanghai');
		
    	$mail = new Mailer(); //PHPMailer对象

    	$mail->SetLanguage('zh_cn');
		//邮件配置
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP();
		$mail->SMTPDebug  	= 1;
		$mail->SMTPAuth   	= true;
		$mail->Host         = $this->conf['ik_mailhost']; 
		$mail->Port       	= $this->conf['ik_mailport'];
		$mail->Username   	= $this->conf['ik_mailuser'];
		$mail->Password   	= $this->conf['ik_mailpwd'];
		
		//POST过来的信息
		$frommail	= $this->conf['ik_mailuser'];
		$fromname	= $this->conf['ik_site_title'];
		$replymail	= $this->conf['ik_mailuser'];
		$replyname	= $this->conf['ik_site_title'];
		$sendname	= '';
		
		if(empty($frommail) || empty($subject) || empty($content) || empty($sendmail)){
			return '0';
		}else{
			
			//邮件发送
			$mail->SetFrom($frommail, $fromname);
			$mail->AddReplyTo($replymail,$replyname);
			$mail->Subject    = $subject;
			$mail->AltBody    = "要查看邮件，请使用HTML兼容的电子邮件阅读器!";
			//$mail->MsgHTML(eregi_replace("[\]",'',$content));
			$mail->MsgHTML(strtr($content,'[\]',''));
			$mail->AddAddress($sendmail, $sendname);
			$mail->Send();

			if($mail->IsError()){
				return '0';
			}
			return '1';
				
		}
	}
}
