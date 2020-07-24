<?php
require "../vendor/autoload.php";

$vk = new VK\Client\VKApiClient();

$oauth = new VK\OAuth\VKOAuth();
$client_id = 7545874;
$redirect_uri = 'https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/surweb/yaml2vk/app/auth.php';
$display = VK\OAuth\VKOAuthDisplay::PAGE;
$scope = [VK\OAuth\Scopes\VKOAuthUserScope::WALL, VK\OAuth\Scopes\VKOAuthUserScope::GROUPS,VK\OAuth\Scopes\VKOAuthUserScope::OFFLINE,VK\OAuth\Scopes\VKOAuthUserScope::MARKET, VK\OAuth\Scopes\VKOAuthUserScope::PHOTOS];
$state = 'secret_state_code';

$browser_url = $oauth->getAuthorizeUrl(VK\OAuth\VKOAuthResponseType::CODE, $client_id, $redirect_uri, $display, $scope, $state);

// print_r($browser_url);
if (empty($_GET)) {
  header( "Location: $browser_url" );
} elseif ($_GET['code']) {
  $oauth = new VK\OAuth\VKOAuth();
    $client_id = 7545874;
    $client_secret = 'ecMkbDVkduJjm2CVdZn4';
    $redirect_uri = 'https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/surweb/yaml2vk/app/auth.php';
    $code = $_GET['code'];

    $response = $oauth->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
    $access_token = $response['access_token'];

    file_put_contents('token.dat', $access_token);
    header( "Location: https://xn-----6kcavojtahc9abe5aii1g0he.xn--p1ai/surweb/yaml2vk/app/yaml2vk.php" );
}