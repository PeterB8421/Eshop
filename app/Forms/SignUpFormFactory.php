<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class SignUpFormFactory {

    use Nette\SmartObject;

    private

    const PASSWORD_MIN_LENGTH = 7;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;

    public function __construct(FormFactory $factory, Model\UserManager $userManager) {
        $this->factory = $factory;
        $this->userManager = $userManager;
    }

    public function create(callable $onSuccess): Form {
        $form = $this->factory->create();
        $form->addText('name', 'Jméno:')->setRequired('Musíte vyplnit své jméno.');
        $form->addText('surname', 'Příjmení:')->setRequired('Musíte vyplnit své příjmení.');
        $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Prosím napište uživateslké jméno.');

        $form->addEmail('email', 'Email:')
                ->setRequired('Prosím napište váš email.');

        $form->addPassword('password', 'Heslo:')
                ->setOption('description', sprintf('minimálně %d znaků', self::PASSWORD_MIN_LENGTH))
                ->setRequired('Musíte vyplnit heslo.')
                ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addText('address', 'Vaše adresa:')->setRequired('Musíte vyplnit adresu.');

        $form->addUpload('photo', 'Profilová fotka:')
                ->addRule($form::IMAGE, "Fotka musí být ve formátu JPEG, PNG nebo GIF.")
                ->addRule($form::MAX_FILE_SIZE, "Maximální velikost souboru je 16 MB.", 16 * 1024000);

        $form->addSubmit('send', 'Registrovat');

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
            $role = "registered";
            if($values->photo->hasFile()){
                $values->photo = $values->photo->getContents();
            }
            else{
                $values->photo = NULL;
            }
            try {
                $this->userManager->add($role, $values->name, $values->username, $values->surname, $values->email, $values->address, $values->password, $values->photo);
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError('Uživatelské jméno již někdo použil.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }

}
