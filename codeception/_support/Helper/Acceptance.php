<?php

namespace codeception\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use codeception\AcceptanceTester;

class Acceptance extends \Codeception\Module
{
    public function authUnit(AcceptanceTester $I)
    {
        $this->authUser($I, [ 1 ]);
    }

    public function authOps(AcceptanceTester $I)
    {
        $this->authUser($I, [ 2 ]);
    }

    public function authHQ(AcceptanceTester $I)
    {
        $this->authUser($I, [ 3 ]);
    }

    public function authAdmin(AcceptanceTester $I)
    {
        $this->authUser($I, [ 4 ]);
    }

    public function authSU(AcceptanceTester $I)
    {
        $this->authUser($I, [ 5 ]);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function authUser(AcceptanceTester $I, $groups = [])
    {
        $user_id = $I->haveInDatabase('users', [
            'username' => 'codeception12',
            'user_group_member' => implode('.', $groups),
            'email' => 'codeception12@gmail.com',
            'password' => '$2y$10$QTWnt/jCk8MyCIYDylS.G.uJNeojuTZbePD/158iKJoPm2U.Hj80y',
            'can_login' => 1,
            'hashed_password' => '',
            'user_first' => '',
            'user_last' => '',
            'contact_number' => '',
            'unit_member' => '',
            'ops_group_member' => '',
            'user_email' => '',
            'ops_mgr' => '',
            'hashed_pwd' => '',
            'status' => 1,
        ]);
        $I->haveInDatabase('active_users', [
            'user_id' => $user_id,
            'session_token' => md5('codeception12'),
            'ip_address' => '127001',
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'expired_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        $I->amOnPage('/login');
        $I->fillField('username', 'codeception12');
        $I->fillField('password', 'codeception12');
        $I->click('Sign In');
    }
}
