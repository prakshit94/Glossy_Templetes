<?php

namespace App\Http\Controllers\Api\v1;

use OpenApi\Attributes as OA;

#[OA\Info(title: "Enterprise Auth API", version: "1.0.0", description: "Complete Enterprise Authentication & Authorization System API")]
#[OA\Server(url: "/api", description: "API Server")]
#[OA\SecurityScheme(securityScheme: "sanctum", type: "apiKey", in: "header", name: "Authorization")]
class OpenApi {}
