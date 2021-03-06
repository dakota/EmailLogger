<?php
App::uses('CakeLogInterface', 'Log');
App::uses('CakeEmail', 'Network/Email');
class EmailLog implements CakeLogInterface {

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

		foreach ($skip as $stringToSkip) {
			if (stripos($errorMessage, $stringToSkip) !== false) {
				return;
			}
		}

		if (empty($types) || in_array($type, $types)) {
			if ($duplicates || (!$duplicates && strpos(file_get_contents($file), $errorMessage) === false)) {
				try {
					$message = "URL: " . Router::url(null, true) . "\r\n";
					$message .= "\r\n=== Error Message ===\r\n" . $errorMessage . "\r\n";
					if (class_exists('AuthComponent') && AuthComponent::user()) {
						$message .= "\r\n=== User details ===\r\n" . print_r(AuthComponent::user(), true) . "\r\n";
					}
					if (!empty($_POST)) {
						$message .= "\r\n=== POST ===\r\n" . var_export($_POST, true) . "\r\n";
					}
					if (!empty($_GET)) {
						$message .= "\r\n=== GET ===\r\n" . var_export($_GET, true) . "\r\n";
					}

					CakeEmail::deliver(null, $subject . $type, $message, $email);
					if (!$duplicates) {
						$output = $message . "\n";
						file_put_contents($file, $output, FILE_APPEND);
					}
				} catch (Exception $e) {
				}
			}
		}
	}
}
