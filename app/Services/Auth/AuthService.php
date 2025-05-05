<?php
namespace App\Services\Auth;
use App\Interfaces\Auth\AuthRepositoryInterface;
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

    public function login(array $credentials)
    {
        // Validate and log in the user
        return $this->authRepository->login($credentials);
    }

    public function logout()
    {
        // Log out the user
        return $this->authRepository->logout();
    }

    //email verification
    public function verifyEmail(string $otp)
    {
        // Validate and verify the email
        $data = $this->authRepository->verifyEmail($otp);

        if(is_array($data) && $data['email_verified_at'] !== null) {
            return $this->responseWithToken($data);
        }else {
            return $data;
        }
    }



    protected function responseWithToken($user)
    {
        // Generate a token for the user and return it
        $token = JWTAuth::fromUser($user);
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
        ];
    }
}
