<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CrmAnalyticsSummaryTool;
use App\Mcp\Tools\CrmCustomerStageDistributionTool;
use App\Mcp\Tools\CustomerN8nCsvExportTool;
use App\Mcp\Tools\WirechatMessagesAnalyticsTool;
use Laravel\Mcp\Server;

class CrmAnalyticsServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Crm Analytics Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        Usa este servidor para obtener resúmenes operativos del CRM por rango de fechas.
        Entrega resultados claros, accionables y en español.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CrmAnalyticsSummaryTool::class,
        CrmCustomerStageDistributionTool::class,
        CustomerN8nCsvExportTool::class,
        WirechatMessagesAnalyticsTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
