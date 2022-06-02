<?php

namespace codeception;

use codeception\AcceptanceTester;

class UnitEditCest
{
    const STATUS_UNIT_ACTIVE   = 1;
    const STATUS_UNIT_ON_HOLD  = 2;
    const STATUS_UNIT_INACTIVE = 3;

    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
    }

    public function veiwGridTest(AcceptanceTester $I)
    {

        $id = $this->createUnit($I);
        $I->amOnPage('/units');
        $I->seeResponseCodeIs(200);
        $I->seeElement("#tr_$id");
    }

    // tests
    public function editUnitTest(AcceptanceTester $I)
    {
        $id = $this->createUnit($I);
        $I->amOnPage('/units/' . $id . '/edit');
        $I->seeResponseCodeIs(200);
        $I->seeInField('unit_name', 'zzzz999');
        $I->fillField('unit_name', 'aaaa999');
        $I->click('Edit Unit');
        $I->seeInDatabase('units', [
            'unit_id' => $id,
            'unit_name' => 'aaaa999',
        ]);
    }
    public function validCurrencyTest(AcceptanceTester $I)
    {
        $id = $this->createUnit($I);
        $I->amOnPage('/units/' . $id . '/edit');
        $I->selectOption('currency_id[]', [1,2]);
        $I->selectOption('default_currency', 1);
        $I->click('Edit Unit');
        $I->seeInDatabase('units', [
            'unit_id' => $id,
            'currency_id' => '1,2',
            'default_currency' => 1,
        ]);
    }
    public function invalidCurrencyTest(AcceptanceTester $I)
    {
        $id = $this->createUnit($I);
        $I->amOnPage('/units/' . $id . '/edit');
        $I->selectOption('currency_id[]', [1]);
        $I->selectOption('default_currency', 2);
        $I->click('Edit Unit');
        $I->seeElement('.alert-danger');
        $I->dontSeeInDatabase('units', [
            'unit_id' => $id,
            'currency_id' => '1',
            'default_currency' => 2,
        ]);
    }

    private function createUnit(AcceptanceTester $I){
       return $I->haveInDatabase('units', [
            'unit_name' => 'zzzz999',
            'details' => '',
            'location' => 'loc999',
            'town' => 'town999',
            'county' => 'county999',
            'unit_manager' => '',
            'contact_number' => '',
            'email' => '',
            'operations_group' => '',
            'users' => '',
            'unitsuppliers' => '',
            'ops_manager_user_id' => '',
            'client_contact_name' => '',
            'client_contact_email' => '',
            'status_id' => self::STATUS_UNIT_ACTIVE,
            'currency_id' => '1',
            'head_count' => 0,
            'time_inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
            'default_currency' => 1,
        ]);
    }
}
