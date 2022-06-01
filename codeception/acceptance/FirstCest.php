<?php
namespace codeception;
use codeception\AcceptanceTester;
class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->assertTrue(true);
    }
}
