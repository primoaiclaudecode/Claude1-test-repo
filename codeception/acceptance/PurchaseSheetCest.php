<?php

namespace codeception;

use codeception\AcceptanceTester;

class PurchaseSheetCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function unitMemberTest(AcceptanceTester $I)
    {
        $nameToShow = $I->generateRandomString(5);
        $idToShow = $this->createUnit($I, $nameToShow);
        $nameNotToShow = $I->generateRandomString(5);
        $this->createUnit($I, $nameNotToShow);
        $this->authUser($I, [ $idToShow ]);
        $I->amOnPage('/sheets/purchases/cash');
        $I->seeResponseCodeIs(200);
        $I->see($nameToShow);
        $I->dontSee($nameNotToShow);
    }

    public function unitOpsGroupTest(AcceptanceTester $I)
    {
        $ops_group = [ 1, 2 ];
        $nameToShow = $I->generateRandomString(5);
        $this->createUnit($I, $nameToShow, [ 1 ]);
        $nameNotToShow = $I->generateRandomString(5);
        $this->createUnit($I, $nameNotToShow, [ 3 ]);
        $this->authUser($I, [], $ops_group);
        $I->amOnPage('/sheets/purchases/cash');
        $I->seeResponseCodeIs(200);
        $I->see($nameToShow);
        $I->dontSee($nameNotToShow);
    }

    public function unitOpsManagerTest(AcceptanceTester $I)
    {
        $user_id = $this->authUser($I, [], [ 1 ]);
        $nameToShow = $I->generateRandomString(5);
        $this->createUnit($I, $nameToShow, [ 3 ], [ $user_id ]);
        $nameNotToShow = $I->generateRandomString(5);
        $this->createUnit($I, $nameNotToShow, [ 3 ]);
        $I->amOnPage('/sheets/purchases/cash');
        $I->seeResponseCodeIs(200);
        $I->see($nameToShow);
        $I->dontSee($nameNotToShow);
    }

    private function authUser(AcceptanceTester $I, $units = [], $ops_group = [])
    {
        $id = $I->haveInDatabase('users', [
            'username' => 'codeception12',
            'user_group_member' => '2',
            'email' => 'codeception12@gmail.com',
            'password' => '$2y$10$QTWnt/jCk8MyCIYDylS.G.uJNeojuTZbePD/158iKJoPm2U.Hj80y',
            'can_login' => 1,
            'hashed_password' => '',
            'user_first' => '',
            'user_last' => '',
            'contact_number' => '',
            'unit_member' => implode(',', $units),
            'ops_group_member' => implode(',', $ops_group),
            'user_email' => '',
            'ops_mgr' => '',
            'hashed_pwd' => '',
            'status' => 1,
        ]);
        $I->amOnPage('/login');
        $I->fillField('username', 'codeception12');
        $I->fillField('password', 'codeception12');
        $I->click('Sign In');

        return $id;
    }

    private function createUnit(AcceptanceTester $I, string $name, $ops_group = [], $ops_manager_user_id = [])
    {
        return $I->haveInDatabase('units', [
            'unit_name' => $name,
            'details' => '',
            'location' => '',
            'town' => '',
            'county' => '',
            'unit_manager' => '',
            'contact_number' => '',
            'email' => '',
            'operations_group' => implode(',', $ops_group),
            'users' => '',
            'unitsuppliers' => '',
            'ops_manager_user_id' => implode(',', $ops_manager_user_id),
            'client_contact_name' => '',
            'client_contact_email' => '',
            'status_id' => 1,
            'currency_id' => '1',
            'head_count' => 0,
            'time_inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
            'default_currency' => 1,
        ]);
    }
}
