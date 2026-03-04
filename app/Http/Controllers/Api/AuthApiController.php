<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthApiController extends Controller
{
    #[OA\Post(
        path: "/api/login",
        summary: "Authenticate user and issue token",
        description: "Login with email and password to get a Bearer token",
        operationId: "authLogin",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "password")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Successful login",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Login berhasil."),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "user",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Admin User"),
                                new OA\Property(property: "email", type: "string", example: "admin@example.com"),
                                new OA\Property(property: "role", type: "string", example: "admin"),
                                new OA\Property(property: "branch_id", type: "integer", nullable: true, example: null)
                            ]
                        ),
                        new OA\Property(property: "token", type: "string", example: "1|AbcDefGhiJklMnoPqrStuVwxYz"),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer")
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "message", type: "string", example: "Email atau Password yang diberikan tidak cocok dengan catatan kami.")
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validation Error"
    )]
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Check password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password yang diberikan tidak cocok dengan catatan kami.',
            ], 401);
        }

        // Generate token
        // The token name can be anything, here we use 'auth_token'
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'branch_id' => $user->branch_id,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Logout user and revoke token",
        description: "Revokes the current access token for the authenticated user",
        operationId: "authLogout",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful logout",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Logout berhasil. Token telah dihapus.")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function logout(Request $request)
    {
        // Delete the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token telah dihapus.'
        ], 200);
    }

    #[OA\Get(
        path: "/api/me",
        summary: "Get the authenticated user's profile",
        description: "Returns the profile information of the currently authenticated user.",
        operationId: "authMe",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Profil pengguna berhasil diambil."),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "user",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Admin User"),
                                new OA\Property(property: "email", type: "string", example: "admin@example.com"),
                                new OA\Property(property: "role", type: "string", example: "admin")
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Eager load branch if available
        if ($user->branch_id) {
            $user->load('branch');
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diambil.',
            'data' => [
                'user' => $user
            ]
        ], 200);
    }
}
