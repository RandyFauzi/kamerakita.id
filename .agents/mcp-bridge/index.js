import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";

const TARGET_URL = "https://kamerakitaid.site/api/mcp";
const SECRET_KEY = process.env.MCP_SECRET_KEY;

if (!SECRET_KEY) {
  console.error("MCP_SECRET_KEY is required in environment variables.");
  process.exit(1);
}

const server = new Server(
  {
    name: "kamerakita-mcp-bridge",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// Forward tools/list to the Laravel API
server.setRequestHandler(ListToolsRequestSchema, async () => {
  try {
    const response = await fetch(TARGET_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${SECRET_KEY}`,
        "Accept": "application/json",
      },
      body: JSON.stringify({ method: "tools/list" }),
    });

    if (!response.ok) {
      const err = await response.text();
      throw new Error(`Failed to list tools: ${response.status} ${response.statusText} - ${err}`);
    }

    const data = await response.json();
    return {
      tools: data.tools || [],
    };
  } catch (error) {
    console.error("Error fetching tools:", error);
    throw error;
  }
});

// Forward tools/call to the Laravel API
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  try {
    const payload = {
      method: "tools/call",
      params: {
        name: request.params.name,
        arguments: request.params.arguments,
      }
    };

    const response = await fetch(TARGET_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${SECRET_KEY}`,
        "Accept": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const err = await response.text();
      throw new Error(`Tool execution failed: ${response.status} - ${err}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    return {
      content: [
        {
          type: "text",
          text: `Error executing tool: ${error.message}`
        }
      ],
      isError: true,
    };
  }
});

// Connect stdio transport
const transport = new StdioServerTransport();
await server.connect(transport);
