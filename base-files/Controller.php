<?php

/**
 * This file is to help you setup your own Controller.php
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="0.0.1",
 *      title="",
 *      description="Documentation ofBackend",
 *      @OA\Contact(
 *          email=""
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\Get(
 *     path="/health",
 *     description="Health Check Page",
 *     @OA\Response(response="default", description="")
 * )
 *
 * @OA\Schema(
 *   schema="Paging",
 *   title="Paging",
 *   @OA\Property(property="totalItems", type="integer", description="Number of total items retrieved", example="200"),
 *   @OA\Property(property="totalPages", type="integer", description="number of total pages", example="5"),
 *   @OA\Property(property="pageSize", type="integer", description="size of page (by 10 per example)", example="10"),
 *   @OA\Property(property="currentPage", type="integer", description="Current page of your pagination", example="2")
 * ),
 *
 *
 * @OA\Schema(
 *   schema="Pagination",
 *   title="Pagination",
 *   @OA\Property(type="integer", title="current_page", property="current_page", example="1", readOnly=true),
 *   @OA\Property(type="string", title="first_page_url", property="first_page_url", example="my_url?page=1"),
 *   @OA\Property(type="integer", title="from", property="from", example=1),
 *   @OA\Property(type="integer", title="last_page", property="last_page", example=1),
 *   @OA\Property(type="string", title="last_page_url", property="last_page_url", example="my_url?page=1"),
 *   @OA\Property(title="links", property="links", type="array",
 *     @OA\Items(type="object",
 *       @OA\Property(type="string", title="url", property="url", example="my_url?page=1"),
 *       @OA\Property(type="string", title="label", property="label", example="1"),
 *       @OA\Property(type="string", title="active", property="active", example="true"),
 *     ),
 *   ),
 *   @OA\Property(type="string", title="next_page_url", property="next_page_url", example="my_url?page=2"),
 *   @OA\Property(type="string", title="path", property="path", example="my_url"),
 *   @OA\Property(type="integer", title="per_page", property="per_page", example=20),
 *   @OA\Property(type="string", title="prev_page_url", property="prev_page_url", example="null"),
 *   @OA\Property(type="integer", title="to", property="to", example=1),
 *   @OA\Property(type="integer", title="total", property="total", example=1),
 * )
 *
 *
 * @OA\Parameter(parameter="page", in="query", name="page", required=false, description="number of page",
 *   @OA\Schema(type="integer", example="1")
 * ),
 * @OA\Parameter(parameter="per_page", in="query", name="per_page", required=false, description="number of items per page",
 *   @OA\Schema(type="integer", example="20")
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
