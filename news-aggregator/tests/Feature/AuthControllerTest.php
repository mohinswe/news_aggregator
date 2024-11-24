<?php
namespace Tests\Feature;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;



class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registers a new user.
     *
     * @return void
     */
    public function test_registers_a_new_user_Successfully()
    {
        // Prepare mock user data
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Ensure the correct route is used
        $response = $this->postJson('/api/register', $userData);

        // Assert response status
        $response->assertStatus(201);

        // Assert the user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);

        // Optionally assert the response structure
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
        ]);
    }

    /**
     * Test requires all fields for registration.
     *
     * @return void
     */
    public function test_requires_all_fields_for_registration()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422); // HTTP 422 Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }


    /**
     * Test login with valid credentials.
     *
     * @return void
     */
    public function test_login_with_valid_credentials()
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => $password,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User logged in successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'token',
            ]);
    }

     /**
     * Test login with invalid credentials.
     *
     * @return void
     */
    public function test_login_with_invalid_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }


    /**
     * Test logout successfully.
     *
     * @return void
     */
    public function test_logout_successfully()
    {
        // Create a mock user with a token
        $user = \App\Models\User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Act: Perform the logout request with the token
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/logout');

        // Assert: Check the response status and message
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User logged out successfully',
            ]);

        // Assert: Check that tokens were deleted
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }


    public function test_forgot_password_successfully()
    {
        // Arrange: Create a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act: Send a POST request to the forgot-password endpoint
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert: Verify the response and ensure email is queued
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset email sent successfully.',
            ]);

        // Optionally, assert email was sent (requires `Illuminate\Support\Facades\Mail`)
        Mail::assertQueued(PasswordReset::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }



   



    

    
}
