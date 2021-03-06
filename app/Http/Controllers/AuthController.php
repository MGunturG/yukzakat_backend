<?php

namespace App\Http\Controllers;

use App\AdditionalHelper\ReturnGoodWay;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class AuthController extends Controller
{

    protected $modelName = 'User';

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'no_telepon' => 'required|max:15',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {

            return ReturnGoodWay::failedReturn(
                'please fill all requirment or check your email is already used or not',
                'bad request'
            );
        }

        $user = new User([
            'name' => $request->name,
            'no_telepon' => $request->no_telepon,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->save();

        if ($user) {
            return ReturnGoodWay::successReturn(
                $user,
                $this->modelName,
                'User successfully created',
                'success'
            );
        } else {
            return ReturnGoodWay::failedReturn(
                'failed',
                'bad request'
            );
        }
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'email' => 'required|string|email',
                'password' => 'required|string',
                'remember_me' => 'boolean'
            ]);

            if($validator->fails()) {

                return ReturnGoodWay::failedReturn(
                    'please fill all requirment',
                    'bad request'
                );
            }

            $credentials = request(['email', 'password']);
            
            if(!Auth::attempt($credentials))
                return ReturnGoodWay::failedReturn(
                    'please check your email or password',
                    'unauthorized'
                );
            
            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addDays(1);
            $token->save();

            return response()->json([
                'token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expired_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]);

        } catch (Exception $err) {
            return $err;
        }
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return ReturnGoodWay::successReturn(
            $request,
            $this->modelName,
            'Logout success',
            'success'
        );
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        if (!$request){
            return ReturnGoodWay::failedReturn(
                'unauthorized',
                'unauthorized'
            );
        }
        return response()->json($request->user());
    }
}
