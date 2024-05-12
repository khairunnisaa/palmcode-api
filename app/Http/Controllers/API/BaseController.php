<?php

namespace App\Http\Controllers\API;

use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller as Controller;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API DOCUMENTATION",
 *      description="Bookings and Countries API",
 *      @OA\Contact(
 *          email="nissa.khairunnisaaa@gmail.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 *  ),
 */
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponseIndex(Builder $query, $message, $perPage = null, $sortBy = null, $sortDirection = 'asc')
    {
        try {
            // Paginate the results
            $paginatedResult = $query->paginate($perPage);

            // Get only the data without pagination metadata
            $data = $paginatedResult->items();

            // Prepare the response data with pagination metadata nested under 'data' key
            $responseData = [
                'success' => true,
                'data' => [
                    'current_page' => $paginatedResult->currentPage(),
                    'data' => $data,
                    'per_page' => $paginatedResult->perPage(),
                    'to' => $paginatedResult->lastItem(),
                    'total' => $paginatedResult->total(),
                ],
                'message' => $message,
            ];

            // Return JSON response
            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            // Handle the exception
            return $this->sendError('Error paginating results: ' . $e->getMessage());
        }
    }

    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
