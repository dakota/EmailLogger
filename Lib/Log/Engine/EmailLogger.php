<?php
App::uses('CakeLogInterface', 'Log');
App::uses('CakeEmail', 'Network/Email');
class EmailLogger implements CakeLogInterface {

	public $config = array(
		'types' => array('warning', 'notice', 'debug', 'info', 'error'),
		'email' => 'email_logger',
		'duplicates' => true,
		'file' => 'email_logger.log',
		'subject' => 'EmailLogger: ',
		'skip' => array(),
	);

    public function __construct($config = array()) {
		$this->config = array_merge($this->config, $config);
		$this->config['file'] = LOGS . $this->config['file'];
    }

    public function write($type, $errorMessage) {
		extract($this->config);

		foreach($skip as $stringToSkip) {
			if(stripos($errorMessage, $stringToSkip) !== false) {
				return;
			}
		}

		if (empty($types) || in_array($type, $types)) {
			if ($duplicates || (!$duplicates && strpos(file_get_contents($file), $errorMessage) === false)) {
				try {
					$message = "URL: " . Router::url(null, true) . "\r\n";
					if(AuthComponent::user()) {
						$message .= "User: " . print_r(AuthComponent::user(), true) . "\r\n";
					}
					$message .= "\r\n== Error Message ==\r\n\r\n" . $errorMessage;

					CakeEmail::deliver(null, $subject . $type, $message, $email);
					if (!$duplicates) {
						$output = $message . "\n";
						file_put_contents($file, $output, FILE_APPEND);
					}
				} catch(Exception $e) {}
			}
		}
    }
}