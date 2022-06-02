<?php

namespace codeception\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use codeception\AcceptanceTester;

class Acceptance extends \Codeception\Module
{
    public function authSU(AcceptanceTester $I)
    {
        $I->haveInDatabase('users', [
            'username' => 'codeception12',
            'user_group_member' => '5',
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
        $I->amOnPage('/login');
        $I->fillField('username', 'codeception12');
        $I->fillField('password', 'codeception12');
        $I->click('Sign In');
    }

    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
