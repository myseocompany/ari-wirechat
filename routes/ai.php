<?php

use App\Mcp\Servers\CrmAnalyticsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/crm-analytics', CrmAnalyticsServer::class)
    ->middleware('mcp.token');
Mcp::local('crm-analytics', CrmAnalyticsServer::class);
