<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\CountryResource;
use Illuminate\Http\JsonResponse;
use Validator;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/countries",
     *      operationId="getCountriesList",
     *      tags={"Countries"},
     *      summary="Get list of countries",
     *      description="Returns a list of countries",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function index(Request $request)
    {
        // Get pagination and sorting parameters from the request
        $perPage = $request->query('perPage');
        $sortBy = $request->query('sortBy');
        $page = $request->query('page', 1); // Get the page parameter, default to page 1 if not provided
        $sortDirection = $request->query('sortDirection', 'asc'); // Default to 'asc' if not provided

        // Ensure that $perPage is not null
        if (!$perPage) {
            $perPage = 10; // Set a default value if $perPage is not provided
        }

        // Retrieve bookings from the database query with eager loading of member and country relationships
        $countriesQuery = Country::query();

        // Apply sorting if sortBy parameter is provided
        if ($sortBy) {
            $countriesQuery->orderBy($sortBy, $sortDirection);
        }

        // Pass the query builder instance to the sendResponseIndex method
        return $this->sendResponseIndex($countriesQuery, 'Countries retrieved successfully', $perPage, $sortBy, $sortDirection);
    }

    /**
     * Store a new country
     *
     * @OA\Post(
     *      path="/countries",
     *      operationId="storeCountry",
     *      tags={"Countries"},
     *      summary="Create a new country",
     *      description="Stores a new country",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","flag_url","code"},
     *              @OA\Property(property="name", type="string", example="United States of America"),
     *              @OA\Property(property="flag_url", type="string", example="https://example.com/usa.jpg"),
     *              @OA\Property(property="code", type="string", example="USA")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Country created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Country")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'flag_url' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10',
        ]);

        $country = Country::create($validatedData);
        return $this->sendResponse(new CountryResource($country), 'Country created successfully.');
    }

    /**
     * @OA\Get(
     *      path="/countries/{id}",
     *      operationId="getCountry",
     *      tags={"Countries"},
     *      summary="Get a specific country",
     *      description="Returns the details of a specific country",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the country",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Country")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Country not found"
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        $country = Country::find($id);

        if (is_null($country)) {
            return $this->sendError('Country not found.');
        }

        return $this->sendResponse(new CountryResource($country), 'Country retrieved successfully.');
    }

    /**
     * @OA\Put(
     *      path="/countries/{id}",
     *      operationId="updateCountry",
     *      tags={"Countries"},
     *      summary="Update an existing country",
     *      description="Updates an existing country",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the country",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","flag_url","code"},
     *              @OA\Property(property="name", type="string", example="United States of America"),
     *              @OA\Property(property="flag_url", type="string", example="https://example.com/usa.jpg"),
     *              @OA\Property(property="code", type="string", example="USA")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Country updated successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Country")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Country not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *        @OA\JsonContent(
     *                              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                              @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}}))
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'flag_url' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // Extract validated data from the request
        $validatedData = $validator->validated();

        // Update the country with the validated data
        $country->update($validatedData);
        return $this->sendResponse(new CountryResource($country), 'Country updated successfully.');
    }

    /**
     * @OA\Delete(
     *      path="/countries/{id}",
     *      operationId="deleteCountry",
     *      tags={"Countries"},
     *      summary="Delete a country",
     *      description="Deletes a country",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the country",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Country deleted successfully"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Country not found"
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $country = Country::findOrFail($id);
        $country->delete();
        return response()->json(['message' => 'Country deleted successfully'], 200);
    }
}
