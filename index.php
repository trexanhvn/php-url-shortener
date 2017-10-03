<?php

require 'config.php';

$url = DEFAULT_URL . '/';

$shortenParam = str_replace('/', '', $_SERVER['REQUEST_URI']);

if (isset($_GET['slug'])) {

	$slug = $_GET['slug'];

	if ('@' == $slug) {
		$url = 'https://www.facebook.com/' . TWITTER_USERNAME;
	} else {

		$slug = preg_replace('/[^a-z0-9]/si', '', $slug);

		if (is_numeric($slug) && strlen($slug) > 8) {
			$url = 'https://www.facebook.com/' . TWITTER_USERNAME . '/status/' . $slug;
		} else {

			$db = new MySQLi(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
			$db->set_charset('utf8mb4');

			$escapedSlug = $db->real_escape_string($slug);
			$redirectResult = $db->query('SELECT url FROM redirect WHERE slug = "' . $escapedSlug . '"');

			if ($redirectResult && $redirectResult->num_rows > 0) {
				$db->query('UPDATE redirect SET hits = hits + 1 WHERE slug = "' . $escapedSlug . '"');
				$url = $redirectResult->fetch_object()->url;
			} else {
				$url = DEFAULT_URL . $_SERVER['REQUEST_URI'];
			}

			$db->close();

		}
	}
} else if ($shortenParam) {
	$db = new MySQLi(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
	$db->set_charset('utf8mb4');

	$redirectResult = $db->query('SELECT url FROM redirect WHERE slug = "' . $shortenParam . '"');

	if ($redirectResult && $redirectResult->num_rows > 0) {
		$db->query('UPDATE redirect SET hits = hits + 1 WHERE slug = "' . $shortenParam . '"');
		$url = $redirectResult->fetch_object()->url;
	} else {
		$url = DEFAULT_URL;
	}
}

header('Location: ' . $url, null, 301);

$attributeValue = htmlspecialchars($url);
?>
<meta http-equiv=refresh content="0;URL=<?php echo $attributeValue; ?>"><a href="<?php echo $attributeValue; ?>">Continue</a><script>location.href=<?php echo json_encode($url, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES); ?></script>
