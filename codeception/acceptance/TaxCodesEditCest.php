<?php

namespace codeception;

use codeception\AcceptanceTester;

class TaxCodesEditCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
    }

    public function viewRecordTest(AcceptanceTester $I)
    {
        $prefix = 'test65';
        $id = $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
            'tax_code_display_rate' => $prefix . '-14.53',
            'currency_id' => 2,
        ]);
        $I->amOnPage('/taxcodes/' . $id . '/edit');
        $I->see('Edit Tax Code');
        $I->seeInField('tax_code_title', $prefix);
        $I->seeInField('tax_code_display_rate', $prefix . '-14.53');
        $I->seeInField('currency_id', 2);
        $I->seeCheckboxIsChecked('credit_purch');
        $I->seeCheckboxIsChecked('vending_sales');
        $I->dontSeeCheckboxIsChecked('credit_sales');
        $I->dontSeeCheckboxIsChecked('cash_purch');
    }

    public function editRecordTest(AcceptanceTester $I)
    {
        $prefix = 'test64';
        $id = $I->haveInDatabase('tax_codes', [
            'tax_code_title' => $prefix,
            'tax_rate' => '14.53',
            'cash_purch' => 0,
            'credit_purch' => 1,
            'credit_sales' => 0,
            'vending_sales' => 1,
            'tax_code_display_rate' => $prefix . '-14.53',
            'currency_id' => 2,
        ]);
        $I->amOnPage('/taxcodes/' . $id . '/edit');
        $I->fillField('tax_code_title', 'test66');
        $I->fillField('tax_code_display_rate', 'test66-66');
        $I->fillField('tax_rate', 14.54);
        $I->selectOption('currency_id', 1);
        $I->checkOption('cash_purch');
        $I->checkOption('credit_sales');
        $I->uncheckOption('vending_sales');
        $I->uncheckOption('credit_purch');
        $I->click('Edit Tax Code');
        $I->seeInDatabase('tax_codes', [
            'tax_code_ID' => $id,
            'tax_code_title' => 'test66',
            'cash_purch' => 1,
            'credit_purch' => 0,
            'credit_sales' => 1,
            'vending_sales' => 0,
            'tax_code_display_rate' => 'test66-66',
            'currency_id' => 1,
        ]);
    }
}
