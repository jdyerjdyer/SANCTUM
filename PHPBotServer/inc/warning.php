<?PHP
	$warning = new warningHandler();

	class warningHandler {
		private $warnings = [];

		function addWarning($warningName, $warningDetails) {
			array_push($warning, [$warningName, $warningDetails]);//NEED TO LOG WARNINGS
		}

		function getLastWarning() {
			if ($numWarnings = count($warnings)) {
				return $warnings[$numWarnings-1];
			} else {
				return false;
			}
		}

		function getAllWarnings() {
			return $warnings;
		}

		function hasWarnings() {
			return (count($warnings) != 0);
		}
	}
?>