<?php

use App\Mcp\Servers\CrmAnalyticsServer;
use App\Http\Middleware\EnsureMcpTokenIsValid;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/crm-analytics', CrmAnalyticsServer::class)
    ->middleware(EnsureMcpTokenIsValid::class);
Mcp::local('crm-analytics', CrmAnalyticsServer::class);
