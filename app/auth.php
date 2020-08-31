<?php
require "../vendor/autoload.php";
include "settings.php";

$vk = new VK\Client\VKApiClient();

$oauth = new VK\OAuth\VKOAuth();
$client_id = CLIENT_ID;
$redirect_uri = REDIRECT_URL;
$display = VK\OAuth\VKOAuthDisplay::PAGE;
$scope = [VK\OAuth\Scopes\VKOAuthUserScope::WALL, VK\OAuth\Scopes\VKOAuthUserScope::GROUPS,VK\OAuth\Scopes\VKOAuthUserScope::OFFLINE,VK\OAuth\Scopes\VKOAuthUserScope::MARKET, VK\OAuth\Scopes\VKOAuthUserScope::PHOTOS];
$state = 'secret_state_code';

$browser_url = $oauth->getAuthorizeUrl(VK\OAuth\VKOAuthResponseType::CODE, $client_id, $redirect_uri, $display, $scope, $state);

// print_r($browser_url);
if (empty($_GET)) {
  header( "Location: $browser_url" );
} elseif ($_GET['code']) {
  $oauth = new VK\OAuth\VKOAuth();
    $client_id = CLIENT_ID;
    $client_secret = CLIENT_SECRET;
    $redirect_uri = REDIRECT_URL;
    $code = $_GET['code'];

    $response = $oauth->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
    $access_token = $response['access_token'];

    file_put_contents('token.dat', $access_token);
    header( "Location: " . REDIRECT_AFTER_AUTH );
}