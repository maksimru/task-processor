<?php

namespace MaksimM\JobProcessor\Tests\Traits;

use Auth;
use Closure;
use MaksimM\JobProcessor\Models\User;

trait AuthenticationTrait
{
    private $authUser = null;

    public function executeWithinAuthentication(Closure $function)
    {
        $this->authenticate();
        $r = $function();
        Auth::logout();

        return $r;
    }

    private function authenticate()
    {
        if (is_null($this->authUser)) {
            $this->authUser = User::find(1) ?? User::create([
                'user_id' => 1,
            ]);
        }
        Auth::setUser($this->authUser);
    }
}
