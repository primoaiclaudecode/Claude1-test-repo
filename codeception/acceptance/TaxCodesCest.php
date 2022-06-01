<?php

namespace codeception;

use codeception\AcceptanceTester;

class TaxCodesCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('username', 'cmcnally');
        $I->fillField('password', 'Dublin2013');
        $I->click('Sign In');
        $I->amOnPage('/taxcodes');
        $I->see('Tax Codes Management');
    }

    // tests
    public function seeValidGridDataTest(AcceptanceTester $I)
    {
        $prefix = 'Test45';
        $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
            'tax_code_display_rate' => $prefix.'-14.53',
            'currency_id' => 1,
        ]);
        $I->sendGet('/taxcodes_data/json');
        $I->seeResponseCodeIs(200);
        $response = $I->grabResponse();
        $response = json_decode($response, true);
        $I->assertArrayHasKey('data', $response);
        $element = end($response['data']);
        $I->assertContains($prefix, $element);
        $I->assertContains($prefix.'-14.53', $element);
        $I->assertContains('14.53', $element);
    }

    public function tryToTest(AcceptanceTester $I)
    {
    }
}
