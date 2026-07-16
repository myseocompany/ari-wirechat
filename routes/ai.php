<?php

use App\Http\Middleware\EnsureMcpTokenIsValid;
use App\Mcp\Servers\CrmAnalyticsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp/crm-analytics', CrmAnalyticsServer::class)
    ->middleware(EnsureMcpTokenIsValid::class);
Mcp::local('crm-analytics', CrmAnalyticsServer::class);
