<?php

namespace codeception;

use codeception\AcceptanceTester;

class CurrencyEditCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
    }

    public function veiwGridTest(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase('currencies', [
            'currency_name' => 'zzzz999',
            'currency_code' => 'TEC',
            'currency_symbol' => '&',
            'is_default' => 0,
        ]);
        $I->amOnPage('/currencies');
        $I->seeResponseCodeIs(200);
        $I->sendGet('/currencies_data/json');
        $I->seeResponseCodeIs(200);
        $response = $I->grabResponse();
        $response = json_decode($response, true);
        $I->assertArrayHasKey('data', $response);
        $element = end($response['data']);
        $I->assertContains($id, $element);
        $I->assertContains('zzzz999', $element);
        $I->assertContains('TEC', $element);
        $I->assertContains('&', $element);
    }

    public function viewRecordTest(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase('currencies', [
            'currency_name' => 'test_curr',
            'currency_code' => 'TEC',
            'currency_symbol' => '&',
            'is_default' => 0,
        ]);
        $I->amOnPage('/currencies/' . $id . '/edit');
        $I->see('Edit Currency');
        $I->seeInField('currency_name', 'test_curr');
        $I->seeInField('currency_code', 'TEC');
        $I->seeInField('currency_symbol', '&');
        $I->dontSeeCheckboxIsChecked('is_default');
    }

    public function changeRecordTest(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase('currencies', [
            'currency_name' => 'test_curr1',
            'currency_code' => 'TE1',
            'currency_symbol' => '#',
            'is_default' => 0,
        ]);
        $I->amOnPage('/currencies/' . $id . '/edit');
        $I->see('Edit Currency');

        $I->fillField('currency_name', 'test_curr2');
        $I->fillField('currency_code', 'TE2');
        $I->fillField('currency_symbol', '@');
        $I->checkOption('is_default');
        $I->click('Edit Currency');
        $I->seeInDatabase('currencies', [
            'currency_id' => $id,
            'currency_name' => 'test_curr2',
            'currency_code' => 'TE2',
            'currency_symbol' => '@',
            'is_default' => 1,

        ]);
        $I->dontSeeInDatabase('currencies', [
            'currency_id !=' => $id,
            'is_default' => 1,
        ]);
        $I->amOnPage('/currencies/1/edit');
        $I->see('Edit Currency');
        $I->checkOption('is_default');
        $I->click('Edit Currency');
    }
}
