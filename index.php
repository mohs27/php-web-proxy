<meta name="google-site-verification=pWatsMNCbuA6w8ziNuq3c6wCJz3ugTq-BdwTKcorJEk" />


<meta name="google-adsense-account" content="ca-pub-9850650985058210"><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9850650985058210"
     crossorigin="anonymous"></script>


<script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>

<meta name="google-adsense-account" content="ca-pub-9850650985058210"><meta name="google-site-verification=pWatsMNCbuA6w8ziNuq3c6wCJz3ugTq-BdwTKcorJEk" />
<?php

define('PROXY_START', microtime(true));

require("vendor/autoload.php");

use Proxy\Http\Request;
use Proxy\Http\Response;
use Proxy\Plugin\AbstractPlugin;
use Proxy\Event\FilterEvent;
use Proxy\Config;
use Proxy\Proxy;

// start the session
session_start();

// load config...
Config::load('./config.php');

// custom config file to be written to by a bash script or something
Config::load('./custom_config.php');

if (!Config::get('app_key')) {
    die("app_key inside config.php cannot be empty!");
}

if (!function_exists('curl_version')) {
    die("cURL extension is not loaded!");
}

// how are our URLs be generated from this point? this must be set here so the proxify_url function below can make use of it
if (Config::get('url_mode') == 2) {
    Config::set('encryption_key', md5(Config::get('app_key') . $_SERVER['REMOTE_ADDR']));
} else if (Config::get('url_mode') == 3) {
    Config::set('encryption_key', md5(Config::get('app_key') . session_id()));
}

// very important!!! otherwise requests are queued while waiting for session file to be unlocked
session_write_close();

// form submit in progress...
if (isset($_POST['url'])) {

    $url = $_POST['url'];
    $url = add_http($url);

    header("HTTP/1.1 302 Found");
    header('Location: ' . proxify_url($url));
    exit;

} else if (!isset($_GET['q'])) {

    // must be at homepage - should we redirect somewhere else?
    if (Config::get('index_redirect')) {

        // redirect to...
        header("HTTP/1.1 302 Found");
        header("Location: " . Config::get('index_redirect'));

    } else {
        if (isset($_GET["tos"]) != "") {
            echo render_template("./templates/tos.php", array(
                'version' => Proxy::VERSION
            ));
        } else {
            echo render_template("./templates/main.php", array('version' => Proxy::VERSION));
        }

    }

    exit;
}

// decode q parameter to get the real URL
$url = url_decrypt($_GET['q']);

$proxy = new Proxy();

// load plugins
foreach (Config::get('plugins', array()) as $plugin) {

    $plugin_class = $plugin . 'Plugin';

    if (file_exists('./plugins/' . $plugin_class . '.php')) {

        // use user plugin from /plugins/
        require_once('./plugins/' . $plugin_class . '.php');

    } else if (class_exists('\\Proxy\\Plugin\\' . $plugin_class)) {

        // does the native plugin from php-proxy package with such name exist?
        $plugin_class = '\\Proxy\\Plugin\\' . $plugin_class;
    }

    // otherwise plugin_class better be loaded already through composer.json and match namespace exactly \\Vendor\\Plugin\\SuperPlugin
    $proxy->getEventDispatcher()->addSubscriber(new $plugin_class());
}

try {

    // request sent to index.php
    $request = Request::createFromGlobals();

    // remove all GET parameters such as ?q=
    $request->get->clear();

    // forward it to some other URL
    $response = $proxy->forward($request, $url);

    // if that was a streaming response, then everything was already sent and script will be killed before it even reaches this line
    $response->send();

} catch (Exception $ex) {

    // if the site is on server2.proxy.com then you may wish to redirect it back to proxy.com
    if (Config::get("error_redirect")) {

        $url = render_string(Config::get("error_redirect"), array(
            'error_msg' => rawurlencode($ex->getMessage())
        ));

        // Cannot modify header information - headers already sent
        header("HTTP/1.1 302 Found");
        header("Location: {$url}");

    } else {

        echo render_template("./templates/main.php", array(
            'url' => $url,
            'error_msg' => $ex->getMessage(),
            'version' => Proxy::VERSION
        ));

    }
}
    
<amp-auto-ads type="adsense"
        data-ad-client="ca-pub-9850650985058210">
</amp-auto-ads>

?>
