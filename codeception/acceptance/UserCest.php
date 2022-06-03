<?php

namespace codeception;

use codeception\AcceptanceTester;

class UserCest
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 2;

    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
    }

    // tests
    public function accessibilityTest(AcceptanceTester $I)
    {
        $user_id = $I->haveInDatabase('users', [
            'username' => 'codeception123',
            'user_group_member' => implode('.', [ '5' ]),
            'user_email' => 'codeception123@gmail.com',
            'password' => '$2y$10$QTWnt/jCk8MyCIYDylS.G.uJNeojuTZbePD/158iKJoPm2U.Hj80y',
            'can_login' => 1,
            'hashed_password' => '',
            'user_first' => '',
            'user_last' => '',
            'contact_number' => '',
            'unit_member' => '',
            'ops_group_member' => '',
            'ops_mgr' => '',
            'hashed_pwd' => '',
            'status' => 1,
        ]);
        $I->amOnPage('/users');
        $I->seeResponseCodeIs(200);
        $I->sendGet('/users_data/json');
        $I->seeResponseCodeIs(200);
        $response = $I->grabResponse();
        $response = json_decode($response, true);
        $I->assertArrayHasKey('data', $response);
        $element = end($response['data']);
        $I->assertContains($user_id, $element);
        $I->assertContains('codeception123', $element);
        $I->assertContains('codeception123@gmail.com', $element);
    }

    public function editTest(AcceptanceTester $I)
    {
        $prefix = $I->generateRandomString(5);
        $user_id = $I->haveInDatabase('users', [
            'username' => 'codeception123'.$prefix,
            'user_group_member' => implode('.', [ '5' ]),
            'user_email' => $prefix.'codeception123@gmail.com',
            'password' => '$2y$10$QTWnt/jCk8MyCIYDylS.G.uJNeojuTZbePD/158iKJoPm2U.Hj80y',
            'can_login' => 1,
            'hashed_password' => '',
            'user_first' => '',
            'user_last' => '',
            'contact_number' => '',
            'unit_member' => '',
            'ops_group_member' => '',
            'ops_mgr' => '',
            'hashed_pwd' => '',
            'status' => self::STATUS_ACTIVE,
        ]);
        $I->amOnPage("/users/$user_id/edit");
        $I->seeResponseCodeIs(200);
        $I->fillField('user_first', $prefix.'userfirst');
        $I->fillField('user_last', $prefix.'userlast');
        $I->fillField('password', 'codeception12');
        $I->selectOption('status', self::STATUS_INACTIVE);
        $I->selectOption('unit_member[]', [125, 129, 130]);
        $I->selectOption('ops_group_member[]', [1,2,3]);
        $I->selectOption('ops_mgr[]', [14, 16, 17]);
        $I->click("Edit User");
        $I->seeResponseCodeIs(200);
        $I->see("User has been updated successfully!");
        $I->seeInDatabase('users', [
            'user_id'=>$user_id,
            'user_first'=>$prefix.'userfirst',
            'user_last'=>$prefix.'userlast',
            'status'=>self::STATUS_INACTIVE,
            'unit_member'=>'125,129,130',
            'ops_group_member'=>'1,2,3',
            'ops_mgr'=>'14,16,17',
        ]);

    }
}
