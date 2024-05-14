<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Country;
use App\Models\IdVerification;
use App\Models\Member;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends BaseController
    /**
     * @OA\Get(
     *      path="/bookings",
     *      operationId="getBookingsList",
     *      tags={"Bookings"},
     *      summary="Get list of bookings",
     *      description="Returns a list of bookings",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
{
    public function index(Request $request)
    {
        try {
            // Get pagination and sorting parameters from the request
            $perPage = $request->query('perPage', 10); // Default to 10 if not provided
            $sortBy = $request->query('sortBy', 'id'); // Default to 'id' if not provided
            $sortDirection = $request->query('sortDirection', 'asc'); // Default to 'asc' if not provided
            $page = $request->query('page', 1); // Default to page 1 if not provided

            // Retrieve bookings from the database query with eager loading of member and country relationships
            $bookingsQuery = Booking::with(['member', 'country']);

            // Apply sorting if sortBy parameter is provided
            if ($sortBy) {
                $bookingsQuery->orderBy($sortBy, $sortDirection);
            }

            // Paginate the query results
            $bookings = $bookingsQuery->paginate($perPage, ['*'], 'page', $page);

            // Construct the response data with related ID verifications
            $responseData = $bookings->map(function($booking) {
                $idVerification = IdVerification::where('member_id', $booking->member_id)->first();

                return [
                    'id' => $booking->id,
                    'member' => $booking->member,
                    'country' => $booking->country,
                    'id_verification' => $idVerification,
                    'surfing_experience' => $booking->surfing_experience,
                    'visit_date' => $booking->visit_date,
                    'desired_board' => $booking->desired_board,
                ];
            });

            // Return paginated response
            return $this->sendResponse($responseData, 'Bookings retrieved successfully', $perPage, $sortBy, $sortDirection);
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->sendError('Error retrieving bookings: ' . $e->getMessage());
        }
    }
    /**
     * @OA\Get(
     *      path="/bookings/{id}",
     *      operationId="getBooking",
     *      tags={"Bookings"},
     *      summary="Get a specific booking",
     *      description="Returns the details of a specific booking",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the booking",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Booking")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Booking not found"
     *      )
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id): JsonResponse
    {
        try {
            // Retrieve the booking
            $booking = Booking::find($id);

            if (is_null($booking)) {
                return $this->sendError('Booking not found.');
            }

            // Retrieve the related member
            $member = Member::find($booking->member_id);

            // Retrieve the related country
            $country = Country::find($booking->country_id);

            // Retrieve the related ID verification using the member_id
            $idVerification = IdVerification::where('member_id', $member->id)->first();

            // Construct the response data
            $responseData = [
                'id' => $booking->id,
                'member' => $booking->member,
                'country' => $booking->country,
                'id_verification' => $idVerification,
                'surfing_experience' => $booking->surfing_experience,
                'visit_date' => $booking->visit_date,
                'desired_board' => $booking->desired_board,
            ];


            return $this->sendResponse($responseData, 'Booking retrieved successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->sendError('Error retrieving booking: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *      path="/bookings",
     *      operationId="storeBooking",
     *      tags={"Bookings"},
     *      summary="Create a new booking",
     *      description="Stores a new booking along with associated member, id verification, and country records",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(property="member_id", type="integer", example=1, description="ID of the member making the booking"),
     *                   @OA\Property(property="country_id", type="integer", example=1, description="ID of the country for the booking"),
     *                   @OA\Property(property="surfing_experience", type="integer", example=8, description="Surfing experience level (1-10+)"),
     *                   @OA\Property(property="visit_date", type="string", format="date", example="2024-05-10", description="Date of the booking"),
     *                   @OA\Property(property="desired_board", type="string", example="longboard", enum={"longboard", "funboard", "shortboard", "fishboard", "gunboard"}, description="Desired type of surfboard"),
     *                   @OA\Property(property="id_card_image", type="string", format="binary", description="Image file of the ID card for verification"),
     *                   @OA\Property(property="link_url_path", type="string", example="http://example.com/1.jpg", description="link image url of ID verification"),
     *               ),
     *           ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Booking created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Booking"),
     *              @OA\Property(property="message", type="string", example="Booking created successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object", example={"member_id": {"The member id field is required."}})
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'country_id' => 'required|exists:countries,id',
            'surfing_experience' => 'required|integer|min:1|max:10',
            'visit_date' => 'required|date',
            'desired_board' => 'required|in:longboard,funboard,shortboard,fishboard,gunboard',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            // Save the id verification record
            $idVerification = IdVerification::create([
                'member_id' => $request->member_id,
                'file_name' => $request->file('id_card_image')->getClientOriginalName(),
                'link_url_path' => $request->file('id_card_image')->store('id_card_images', 'public'), // Store the image file
            ]);

            // Attempt to create the Booking record
            $booking = Booking::create([
                'member_id' => $request->member_id,
                'country_id' => $request->country_id,
                'id_verification_id' => $idVerification->id,
                'surfing_experience' => $request->surfing_experience,
                'visit_date' => $request->visit_date,
                'desired_board' => $request->desired_board,
            ]);

            // Retrieve the associated member and country records
            $member = Member::findOrFail($request->member_id);
            $country = Country::findOrFail($request->country_id);

            // Construct the response data
            $responseData = [
                'id' => $booking->id,
                'member' => $member,
                'country' => $country,
                'id_verification' => $idVerification,
                'surfing_experience' => $booking->surfing_experience,
                'visit_date' => $booking->visit_date,
                'desired_board' => $booking->desired_board,
            ];

            // Return success response with the created booking data
            return $this->sendResponse($responseData, 'Booking created successfully.');
        } catch (\Exception $e) {
            // Return error response if any exception occurs
            return $this->sendError('Booking creation failed.', $e->getMessage());
            }
    }

    /**
     * @OA\Put(
     *      path="/bookings/{id}",
     *      operationId="updateBooking",
     *      tags={"Bookings"},
     *      summary="Update an existing booking",
     *      description="Updates an existing booking",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the booking",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *                mediaType="multipart/form-data",
     *                @OA\Schema(
     *                    @OA\Property(property="member_id", type="integer", example=1, description="ID of the member making the booking"),
     *                    @OA\Property(property="country_id", type="integer", example=1, description="ID of the country for the booking"),
     *                    @OA\Property(property="surfing_experience", type="integer", example=8, description="Surfing experience level (1-10+)"),
     *                    @OA\Property(property="visit_date", type="string", format="date", example="2024-05-10", description="Date of the booking"),
     *                    @OA\Property(property="desired_board", type="string", example="longboard", enum={"longboard", "funboard", "shortboard", "fishboard", "gunboard"}, description="Desired type of surfboard"),
     *                    @OA\Property(property="id_card_image", type="string", format="binary", description="Image file of the ID card for verification"),
     *                    @OA\Property(property="link_url_path", type="string", example="http://example.com/1.jpg", description="link image url of ID verification"),
     *                ),
     *            ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Booking updated successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Booking")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Booking not found"
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
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'member_id' => 'exists:members,id',
            'country_id' => 'exists:countries,id',
            'id_verification_id' => 'nullable|exists:id_verifications,id',
            'surfing_experience' => 'integer|min:1|max:10',
            'visit_date' => 'date',
            'desired_board' => 'in:longboard,funboard,shortboard,fishboard,gunboard',
            'id_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust maximum file size as needed
            'link_url_path' => 'nullable|url',
        ]);


        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }
        // Update the booking attributes from the request
        $booking->update($request->except('id_card_image', 'link_url_path'));
        // Handle id_card_image upload
        if ($request->hasFile('id_card_image')) {
            $image = $request->file('id_card_image');
            $fileName = $image->getClientOriginalName();
            // Retrieve the Member associated with the Booking
            $member = $booking->member;
            // Update the id_verification record with the new file name
            $member->idVerifications()->update(['file_name' => $fileName]);
        }

        // Update link_url_path if provided in the request
        if ($request->has('link_url_path')) {
            // Retrieve the Member associated with the Booking
            $member = $booking->member;
            // Update the id_verification record with the new file name
            $member->idVerifications()->update(['link_url_path' => $request->link_url_path]);
        }

        return $this->sendResponse(new BookingResource($booking), 'Booking updated successfully.', 200);
    }

    /**
     * @OA\Delete(
     *      path="/bookings/{id}",
     *      operationId="deleteBooking",
     *      tags={"Bookings"},
     *      summary="Delete a booking",
     *      description="Deletes a booking",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the booking",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Booking deleted successfully"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Booking not found"
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return response()->json(['message' => 'Booking deleted successfully'], 200);
    }

    public function search(Request $request): JsonResponse
    {
        $query = Booking::query();

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $bookings = $query->get();
        return response()->json(['bookings' => $bookings], 200);
    }

    public function sort(Request $request): JsonResponse
    {
        $column = $request->input('column');
        $direction = $request->input('direction', 'asc');

        $bookings = Booking::orderBy($column, $direction)->get();
        return response()->json(['bookings' => $bookings], 200);
    }

    public function paginate(Request $request): JsonResponse
    {
        $perPage = $request->input('perPage', 10);

        $bookings = Booking::paginate($perPage);
        return response()->json(['bookings' => $bookings], 200);
    }
}

