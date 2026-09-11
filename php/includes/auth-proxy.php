<?php
/**
 * Fail-closed WordPress authentication fallback.
 * The optional transport supports isolated tests; request data cannot supply it.
 */
function cg_auth_proxy_check(
    string $url,
    string $secret,
    string $username,
    string $password,
    ?callable $transport = null
): bool {
    if ($url === '' || $secret === '') {
        error_log('[CG auth proxy] configuration_missing');
        return false;
    }
    $payload = json_encode(['action' => 'wp_auth_check', 'username' => $username, 'password' => $password]);
    if ($payload === false) {
        error_log('[CG auth proxy] request_encoding_failed');
        return false;
    }
    $options = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nX-CG-Secret: " . $secret,
        'content' => $payload,
        'timeout' => 10,
        // Never forward credentials to a redirect target.
        'follow_location' => 0,
        'ignore_errors' => true,
    ]];
    try {
        if ($transport !== null) {
            [$raw, $status] = $transport($url, $options);
        } else {
            $http_response_header = [];
            $raw = @file_get_contents($url, false, stream_context_create($options));
            $status = 0;
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $match)) {
                    $status = (int) $match[1];
                }
            }
        }
    } catch (\Throwable $e) {
        // Exception messages can contain URLs, credentials, or response content.
        error_log('[CG auth proxy] transport_failed');
        return false;
    }
    if ($status !== 0 && ($status < 200 || $status >= 300)) {
        error_log('[CG auth proxy] http_failed status=' . (int) $status);
        return false;
    }
    if ($raw === false || $status === 0) {
        error_log('[CG auth proxy] transport_failed');
        return false;
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['success']) || !is_bool($data['success'])) {
        error_log('[CG auth proxy] response_invalid');
        return false;
    }
    error_log($data['success'] ? '[CG auth proxy] authenticated' : '[CG auth proxy] rejected');
    return $data['success'];
}