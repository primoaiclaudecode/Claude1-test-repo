<?php

namespace codeception;

use codeception\AcceptanceTester;

class TaxCodesGridCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
        $I->amOnPage('/taxcodes');
        $I->see('Tax Codes Management');
    }

    // tests
    public function insertRowTest(AcceptanceTester $I)
    {
        $prefix = 'zzz99';
        $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
            'tax_code_display_rate' => $prefix . '-14.53',
            'currency_id' => 2,
        ]);
        $I->sendGet('/taxcodes_data/json');
        $I->seeResponseCodeIs(200);
        $response = $I->grabResponse();
        $response = json_decode($response, true);
        $I->assertArrayHasKey('data', $response);
        $element = end($response['data']);
        $I->assertContains($prefix, $element);
        $I->assertContains($prefix . '-14.53', $element);
        $I->assertContains('14.53', $element);
        $I->assertContains('Sterling', $element);
    }

    public function changeRowTest(AcceptanceTester $I)
    {
        $prefix = 'Test46';
        $id = $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
            'tax_code_display_rate' => $prefix . '-14.53',
            'currency_id' => 1,
        ]);
        $prefix = 'Test47';
        $id1 = $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 1,
            'credit_purch' => 0,
            'credit_sales' => 1,
            'vending_sales' => 0,
            'tax_code_display_rate' => $prefix . '-14.53',
            'currency_id' => 1,
        ]);
        $I->sendPost('/taxcodes_data/apply', [
            '_method' => 'get',
            'taxCodes[cash_purch][]' => $id,
            'taxCodes[credit_sales][]' => $id,
            'taxCodes[credit_purch][]' => $id1,
            'taxCodes[vending_sales][]' => $id1,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('tax_codes', [
            'tax_code_ID' => $id,
            'cash_purch' => 1,
            'credit_purch' => 0,
            'credit_sales' => 1,
            'vending_sales' => 0,
        ]);
        $I->seeInDatabase('tax_codes', [
            'tax_code_ID' => $id1,
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
        ]);
    }
}
