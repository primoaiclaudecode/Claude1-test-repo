<?php

namespace codeception;

use codeception\AcceptanceTester;

class FilesCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->authSU($I);
    }

    // tests
    public function createUploadTest(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase('file_system', [
            'dir_file_name' => 'AACodeception21',
            'is_dir' => 1,
            'parent_dir_id' => 0,
            'dir_path' => 'AACodeception21/',
            'file_type' => null,
        ]);
        $I->amOnPage('/files');
        $I->seeResponseCodeIs(200);
        $I->see('AACodeception21');
        $I->click('AACodeception21');
        $I->see('Upload File');
        $I->see('Create Directory');
        $name = 'codecept_fold' . $I->generateRandomString(6);
        $I->fillField('folder_name', $name);
        $I->click("//button[@id='create_folder_btn']");
        $I->seeElement('.alert-success');
        $I->see('The directory was successfully created.');
        $I->click($name);
        $I->attachFile('input[name=file_name]', 'logo-square.png');
        $I->click("//button[@id='upload_file_btn']");
        $I->see('The file was successfully uploaded.');
        $I->see('logo-square.png');
        $I->seeFileFound('logo-square.png','file_share/AACodeception21/'.$name.'/');
    }

    public function deleteTest(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase('file_system', [
            'dir_file_name' => 'AACodeception22',
            'is_dir' => 1,
            'parent_dir_id' => 0,
            'dir_path' => 'AACodeception22/',
            'file_type' => null,
        ]);
        $I->amOnPage('/files');
        $I->seeResponseCodeIs(200);
        $I->see('AACodeception22');

        $I->click("//a[@id='delete_file_$id']");
        $I->seeElement('.alert-success');
        $I->see('The directory was successfully deleted.');
    }

    public function downloadTest(AcceptanceTester $I)
    {
        $name = 'codecept_fold' . $I->generateRandomString(6);
        $dir_id = $I->haveInDatabase('file_system', [
            'dir_file_name' => $name,
            'is_dir' => 1,
            'parent_dir_id' => 0,
            'dir_path' => $name.'/',
            'file_type' => null,
        ]);
        $I->amOnPage('/files');
        $I->seeResponseCodeIs(200);
        $I->see($name);
        $I->click($name);
        $I->attachFile('input[name=file_name]', 'logo-square.png');
        $I->click("//button[@id='upload_file_btn']");
        $I->see('The file was successfully uploaded.');
        $I->seeInDatabase('file_system', [
            'dir_file_name' => 'logo-square.png',
            'is_dir' => 0,
            'parent_dir_id' => $dir_id,
            'dir_path' => $name.'/',
            'file_type' => 'image/png',
        ]);
        $I->click("//button[@name='submit_download']");
        $I->seeResponseCodeIs(200);
    }
}
