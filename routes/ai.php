<?php

use App\Mcp\Servers\CrmAnalyticsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/crm-analytics', CrmAnalyticsServer::class);
Mcp::local('crm-analytics', CrmAnalyticsServer::class);
