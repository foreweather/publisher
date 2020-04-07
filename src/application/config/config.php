<?php
//  Add application configuration
return [
    'queue'        => [
        'host' => getenv('QUEUE_HOST'),
    ],
    'oauth_client' => [
        'client_id'              => getenv('OAUTH_CLIENT_ID'),    // The client ID assigned to you by the provider
        'client_secret'          => getenv('OAUTH_CLIENT_SECRET'),   // The client password assigned to you by the
        'access_token_url'       => getenv('OAUTH_TOKEN_URL'),
        'scope'                  => [getenv('OAUTH_CLIENT_SCOPE')],
        'url'                    => getenv('API_BASE_URL'),
    ],
];
