<?php

require_once './vendor/php-github-api/lib/Github/Autoloader.php';
require_once './lib/hugs.php';

$config = include './config.php';

Github_Autoloader::register();

$github = new Github_Client();

$following = $github->getUserApi()->getFollowing($config['github']['login']);

if (is_array($following) && count($following)) {
  // Pick one at random.
  $randomPerson = $following[array_rand($following)];

  // Get a list of their repos.
  $repos = $github->getRepoApi()->getUserRepos($randomPerson);

  if (is_array($repos) && count($repos)) {
    // Get the most recently changed repo.
    usort($repos, function($a, $b) { return strtotime($b['pushed_at']) - strtotime($a['pushed_at']); });
    $repo = $repos[0];

    $branch = isset($repo['master_branch']) ? $repo['master_branch'] : 'master';

    $commits = $github->getCommitApi()->getBranchCommits($repo['owner'], $repo['name'], $branch);

    $hug = array(
      'hug' => Hugs::random(),
      'url' => 'https://github.com/' . $repo['owner'] . '/' . $repo['name'],
      'project' => $repo['owner'] . '/' . $repo['name'],
      'time' => time(),
      'commit' => $commits[0]
    );

    $cookieJar = tempnam("~", "CURLCOOKIE");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_URL, 'https://github.com/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = trim(curl_exec($ch));

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = FALSE;
    $dom->strictErrorChecking = FALSE;
    @$dom->loadHTML($html);

    // TODO: Use XPath, duh.
    $token = FALSE;
    if ($input = getElementByName($dom, 'input', 'authenticity_token')) {
      $token = $input->getAttribute('value');
    }

    $post = array(
      'authenticity_token' => $token,
      'login' => $config['github']['login'],
      'password' => $config['github']['password']
    );

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_URL, 'https://github.com/session');
    $html = trim(curl_exec($ch));

    curl_setopt($ch, CURLOPT_POSTFIELDS, FALSE);
    curl_setopt($ch, CURLOPT_URL, 'https://github.com' . $hug['commit']['url']);
    $html = trim(curl_exec($ch));

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $commit_id = FALSE;
    if ($input = getElementByName($dom, 'input', 'commit_id')) {
      $commit_id = $input->getAttribute('value');
    }

    $token = FALSE;
    if ($input = getElementByName($dom, 'input', 'authenticity_token')) {
      $token = $input->getAttribute('value');
    }

    $post = array(
      'authenticity_token' => $token,
      'commit_id' => $commit_id,
      'comment[body]' => $hug['hug']
    );

    curl_setopt($ch, CURLOPT_URL, $hug['url'] . '/commit_comment/create');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    $html = trim(curl_exec($ch));

    try {
      $m = new Mongo($config['db']['connection_string']);
      $db = $m->selectDB($config['db']['name']);
      $db->hugs->insert($hug);

      print_r($hug);
    }
    catch (MongoConnectionException $e) {
      echo '<h1>Database connection failure</h1><hr><p>I blame myself :(';
      exit();
    }
  }
}

function getElementByName($dom, $tag, $name) {
  $xpath = new DOMXpath($dom);
  $nodeList = $xpath->query('//' . $tag . '[contains(@name, "' . $name . '")]');
  foreach ($nodeList as $node) {
    if ($node->getAttribute('name') == $name) {
      return $node;
    }
  }
  return FALSE;
}
