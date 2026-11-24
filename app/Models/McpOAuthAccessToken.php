<?php

namespace App\Models;

use Laravel\Passport\Token;

class McpOAuthAccessToken extends Token
{
    protected $table = 'oauth_access_tokens';
}
