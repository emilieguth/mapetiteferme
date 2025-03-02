<?php
namespace mail;

require_once __DIR__.'/PHPMailer-6.5.1/src/PHPMailer.php';
require_once __DIR__.'/PHPMailer-6.5.1/src/SMTP.php';
require_once __DIR__.'/PHPMailer-6.5.1/src/Exception.php';

class MailLib {

	protected ?string $fromEmail = NULL;
	protected ?string $fromName = NULL;
	protected array $to = [];
	protected array $cc = [];
	protected array $bcc = [];
	protected ?string $replyTo = NULL;
	protected ?string $bodyText = NULL;
	protected ?string $bodyHtml = NULL;
	protected ?string $subject = NULL;
	protected ?string $template = NULL;

	protected array $attachments = [];

	public function __construct() {
	}

	public function setFromEmail(string $fromEmail): MailLib {
		$this->fromEmail = $fromEmail;
		return $this;
	}

	public function setReplyTo(string $email): MailLib {
		$this->replyTo = $email;
		return $this;
	}

	public function setFromName(string $fromName): MailLib {
		$this->fromName = $fromName;
		return $this;
	}

	public function setTemplate(string $template): MailLib {
		$this->template = $template;
		return $this;
	}

	public function addAttachment(string $content, string $name, string $type): MailLib {
		$this->attachments[] = [
			'content' => $content,
			'name' => $name,
			'type' => $type
		];
		return $this;
	}

	public function addTo(string $to): MailLib {
		$this->to[] = $to;
		return $this;
	}

	public function addCc(string $email): MailLib {
		$this->cc[] = $email;
		return $this;
	}

	public function addBcc(string $email): MailLib {
		$this->bcc[] = $email;
		return $this;
	}

	public function setContent(string $subject, ?string $bodyText = NULL, ?string $bodyHtml = NULL): MailLib {

		$this->subject = $subject;
		$this->bodyText = $bodyText;
		$this->bodyHtml = $bodyHtml;

		return $this;

	}

	public function setBodyText(string $bodyText): MailLib {
		$this->bodyText = $bodyText;
		return $this;
	}

	public function setBodyHtml(string $bodyHtml): MailLib {
		$this->bodyHtml = $bodyHtml;
		return $this;
	}

	public function setSubject(string $subject): MailLib {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Reset mail parameters.
	 */
	public function reset() {
		$this->fromEmail = NULL;
		$this->fromName = NULL;
		$this->to = [];
		$this->cc = [];
		$this->bcc = [];
		$this->replyTo = NULL;
		$this->bodyText = '';
		$this->bodyHtml = '';
		$this->subject = '';
		$this->attachments = [];
	}

	/**
	 * Send the mail.
	 */
	public function send(string $server): MailLib {

		if(
			empty($this->to) or
			(empty($this->bodyText) and empty($this->bodyHtml)) or
			empty($this->subject)
		) {

			trigger_error('Internal error: it needs at least to set: a to address ('.var_export($this->to, true).'), a subject ('.var_export($this->subject, true).'), a body (text or html) to send a mail.', E_USER_ERROR);

		} else {

			// /!\ Do not touch
			if(LIME_ENV === 'prod') {

				$toSelected = $this->to;

			// Can't send all mails
			} else {

				$toSelected = [];

				foreach($this->to as $to) {

					if(in_array($to, \Setting::get('mail\devSendOnly'))) {
						$toSelected[] = $to;
					}

				}

				if($toSelected === []) {
					return $this;
				}

				$this->subject = '['.LIME_ENV.'] '.$this->subject;

			}

			$eEmail = new \mail\Email([
				'html' => $this->bodyHtml,
				'text' => $this->bodyText,
				'subject' => $this->subject,
				'server' => $server,
				'fromEmail' => $this->fromEmail,
				'fromName' => $this->fromName ?? \Setting::get('mail\emailName'),
				'to' => $toSelected,
				'cc' => $this->cc,
				'bcc' => $this->bcc,
				'replyTo' => $this->replyTo,
				'attachments' => serialize($this->attachments)
			]);

			\mail\Email::model()->insert($eEmail);

			if(LIME_ENV === 'dev') {
				$this->doSend($eEmail);
			}
		}

		$this->reset();

		return $this;


	}

	/**
	 * Sends all waiting emails
	 *
	 */
	public static function sendWaiting() {

		$cEmail = \mail\Email::model()
			->select('id', 'html', 'text', 'subject', 'server', 'fromEmail', 'fromName', 'to', 'cc', 'bcc', 'replyTo', 'attachments')
			->whereStatus(\mail\Email::WAITING)
			->getCollection();

		foreach($cEmail as $eEmail) {

			$affected = \mail\Email::model()
				->whereStatus(Email::WAITING)
				->update($eEmail, ['status' => \mail\Email::SENDING]);

			if($affected > 0) {
				self::doSend($eEmail);
			}

		}

	}

	private static function doSendWithAPI(Email $eEmail) {

		$server = \Setting::get('mail\smtpServers')[$eEmail['server']];

		$headers = [
			'Accept: application/json',
			'Content-Type: application/json',
			'Api-Key: '.$server['apiKey'],
		];

		$to = [];
		foreach($eEmail['to'] as $email) {
			$to[] = ['email' => $email];
		}

		$data = [
			'sender' => [
				'name' => $eEmail['fromName'],
				'email' => $eEmail['fromEmail'] ?? $server['from'],
			],
			'to' => $to,
			'subject' => $eEmail['subject'],
			'htmlContent' => $eEmail['html'],
			'textContent' => $eEmail['text'],
		];

		if (count($eEmail['cc']) > 0) {

			$cc = [];
			foreach($eEmail['cc'] as $email) {
				$cc[] = ['email' => $email];
			}

			$data['cc'] = $cc;

		}

		if (count($eEmail['bcc']) > 0) {

			$bcc = [];
			foreach($eEmail['bcc'] as $email) {
				$bcc[] = ['email' => $email];
			}

			$data['bcc'] = $bcc;
		}

		if($eEmail['replyTo'] !== NULL) {
			$data['replyTo'] = ['email' => $eEmail['replyTo']];
		}

		$attachments = unserialize($eEmail['attachments']);

		if(count($attachments) > 0) {

			$data['attachment'] = [];
			foreach($attachments as $attachment) {
				$data['attachment'][] = ['content' => base64_encode($attachment['content']), 'name' => $attachment['name']];
			}

		}

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => $server['apiUrl'],
			CURLOPT_HEADER => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => json_encode($data),
		]);

		curl_exec($ch);

		curl_close($ch);
	}
	private static function doSendWithSMTP(Email $eEmail) {

		$server = \Setting::get('mail\smtpServers')[$eEmail['server']];

		foreach($eEmail['to'] as $to) {

			$mail = new \PHPMailer\PHPMailer\PHPMailer();
			$mail->Host = $server['host'];
			$mail->Port = $server['port'];
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();
			$mail->SMTPAuth = TRUE;
			$mail->AuthType = 'LOGIN';
			$mail->Username = $server['user'];
			$mail->Password = $server['password'];
			$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

			$mail->setFrom($eEmail['fromEmail'] ?? $server['from'], $eEmail['fromName']);
			$mail->addAddress($to);

			if($eEmail['replyTo'] !== NULL) {
				$mail->addReplyTo($eEmail['replyTo']);
			}

			foreach($eEmail['cc'] as $email) {
				$mail->addCC($email);
			}

			foreach($eEmail['bcc'] as $email) {
				$mail->addBCC($email);
			}

			$attachments = unserialize($eEmail['attachments']);

			foreach($attachments as $attachment) {
				$mail->addStringAttachment($attachment['content'], $attachment['name'], type: $attachment['type']);
			}

			//Content
			if($eEmail['html']) {
				$mail->isHTML(TRUE);
				$mail->Subject = $eEmail['subject'];
				$mail->Body = $eEmail['html'];
				$mail->AltBody = $eEmail['text'];
			} else {
				$mail->Subject = $eEmail['subject'];
				$mail->Body = $eEmail['text'];
			}

			$mail->send();

		}
	}

	private static function doSend(Email $eEmail) {

		$server = \Setting::get('mail\smtpServers')[$eEmail['server']];

		if($server['mode'] === 'api') {

			self::doSendWithAPI($eEmail);

 		} else {

			self::doSendWithSMTP($eEmail);

		}

		$eEmail['sentAt'] = new \Sql('NOW()');

		\mail\Email::model()
			->update($eEmail, [
				'status' => Email::SUCCESS,
				'sentAt' => new \Sql('NOW()')
			]);

	}

	public static function clean() {

		\mail\Email::model()
			->whereStatus(\mail\Email::SUCCESS)
			->where('sentAt < NOW() - INTERVAL 1 WEEK')
			->delete();

	}

}
?>
