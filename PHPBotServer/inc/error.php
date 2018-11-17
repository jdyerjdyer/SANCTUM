<?PHP
	$error = new errorHandler();

	class errorHandler {
		private $errors = [];

		function addError($errorName, $errorDetails) {
			array_push($errors, [$errorName, $errorDetails]);//NEED TO LOG ERRORS
		}

		function getLastError() {
			if ($numErrs = count($errors)) {
				return $errors[$numErrs-1];
			} else {
				return false;
			}
		}

		function getAllErrors() {
			return $errors;
		}

		function hasErrors() {
			return (count($errors) != 0);
		}
	}
?>