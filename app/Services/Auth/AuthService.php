<?php
namespace App\Services\Auth;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data)
    {
        // Validate and register the user
        return $this->authRepository->register($data);

    }

    //login
    /**
     * @param array $credentials
     * @return array|string
     */
    public function login(array $credentials)
    {
        $token = JWTAuth::attempt($credentials);
        //check if user is banned
        if (Auth::check() && Auth::user()->banned_at) {
            Auth::logout();
            return 'user_banned';
        }
        if (!$token) {
            return 'invalid_credentials';
        }

        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            return 'email_not_verified';
        }

        return $this->responseWithToken($user, $token);
    }

    //email verification
    public function verifyEmail(string $otp) : array
    {
        try{
            // Validate and verify the email
        $data = $this->authRepository->verifyEmail($otp);
        // dd($data['user']->email_verified_at);
        if ($data['success'] === false) {
            return [
                'success' => false,
                'message' => $data['message'],
                'code' => $data['code'],
            ];
        }

        if($data['user'] instanceof User && $data['user']->email_verified_at !== null) {
            $token = JWTAuth::fromUser($data['user']);
            return [
                'success' => true,
                'message' => 'Email verified successfully.',
                'code' => 200,
                'data' => $this->responseWithToken($data['user'], $token),
            ];
        }

        // Fallback for unexpected cases
        return [
            'success' => false,
            'message' => 'Unexpected error occurred.',
            'code' => 500,
        ];

        }catch(\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to verify email.',
                'code' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    //logout
    public function logout()
    {
        // Logout the user
        try{
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->success(null, 'Logout successful.');
        }catch(\Exception $e) {
            return response()->error('Failed to logout.', 500, $e->getMessage());
        }
    }

    //resend otp
    /**
     * @param string $email
     * @return array
     */
    public function resendOtp(string $email)
    {
        return $this->authRepository->resendOtp($email);
    }

    //reset password
    /**
     * @param array $password
     * @return array
     */
    public function resetPassword($password):array
    {

        return $this->authRepository->resetPassword($password);
    }


    //response with token
    /**
     * @param User $user
     * @param string $token
     * @return array
     */
    protected function responseWithToken($user, $token) : array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
        ];
    }

    //update password
    /**
     * @param string $password
     * @return array
     */
    public function updatePassword(Array $data):array
    {
        return $this->authRepository->updatePassword($data);
    }


    //update profile
    /**
     * @param array $data
     * @return array
     */
    public function updateProfile(array $data): array
    {
        return $this->authRepository->updateProfile($data);
    }

    //me
    /**
     * @return array
     */
    public function me($id):array
    {
        return $this->authRepository->me($id);
    }
}
