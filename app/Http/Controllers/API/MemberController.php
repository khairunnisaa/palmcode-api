<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\MemberResource;
use Illuminate\Http\JsonResponse;
use Validator;
use Illuminate\Http\Request;
use App\Models\Member;

class MemberController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/members",
     *      operationId="getMembersList",
     *      tags={"Members"},
     *      summary="Get list of members",
     *      description="Returns a list of members",
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

        // Retrieve members from the database query with eager loading of bookings and idVerifications relationships
        $membersQuery = Member::query()->with(['bookings', 'idVerifications']);

        // Apply sorting if sortBy parameter is provided
        if ($sortBy) {
            $membersQuery->orderBy($sortBy, $sortDirection);
        }

        // Pass the query builder instance to the sendResponseIndex method
        return $this->sendResponseIndex($membersQuery, 'Members retrieved successfully', $perPage, $sortBy, $sortDirection);
    }

    /**
     * Store a new member
     *
     * @OA\Post(
     *      path="/members",
     *      operationId="storeMember",
     *      tags={"Members"},
     *      summary="Create a new member",
     *      description="Stores a new member",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","whatsapp_number"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="whatsapp_number", type="string", example="+1234567890")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Member created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
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
            'email' => 'required|email|unique:members,email',
            'whatsapp_number' => 'required|string|max:20',
        ]);

        $member = Member::create($validatedData);
        return $this->sendResponse(new MemberResource($member), 'Member created successfully.');
    }

    /**
     * @OA\Get(
     *      path="/members/{id}",
     *      operationId="getMember",
     *      tags={"Members"},
     *      summary="Get a specific member",
     *      description="Returns the details of a specific member",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the member",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Member not found"
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        $member = Member::find($id);

        if (is_null($member)) {
            return $this->sendError('Member not found.');
        }

        return $this->sendResponse(new MemberResource($member), 'Member retrieved successfully.');
    }

    /**
     * @OA\Put(
     *      path="/members/{id}",
     *      operationId="updateMember",
     *      tags={"Members"},
     *      summary="Update an existing member",
     *      description="Updates an existing member",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the member",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","whatsapp_number"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="whatsapp_number", type="string", example="+1234567890")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Member updated successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Member")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Member not found"
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
        $member = Member::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'whatsapp_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // Extract validated data from the request
        $validatedData = $validator->validated();

        // Update the member with the validated data
        $member->update($validatedData);
        return $this->sendResponse(new MemberResource($member), 'Member updated successfully.');
    }

    /**
     * @OA\Delete(
     *      path="/members/{id}",
     *      operationId="deleteMember",
     *      tags={"Members"},
     *      summary="Delete a member",
     *      description="Deletes a member",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the member",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Member deleted successfully"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Member not found"
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $member = Member::findOrFail($id);
        $member->delete();
        return response()->json(['message' => 'Member deleted successfully'], 200);
    }
}
