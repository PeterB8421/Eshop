<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\ProductManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class UserPresenter extends BasePresenter {

    private $userManager;

    function __construct(\App\Model\UserManager $usrMgr) {
        $this->userManager = $usrMgr;
    }

    public function renderDefault($order = 'id'): void {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro práci s uživateli musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        } elseif ($this->getUser()->getIdentity()->roles[0] != "admin") {
            $this->flashMessage("Pro zobrazení seznamu uživatelů nemáte dostatečná práva", "error");
            $this->redirect("Product:default");
        }
        $this->template->users = $this->userManager->getAll($order);
        $this->template->user = $this->getUser();
        \Tracy\Debugger::barDump($this->template->user);
    }

    public function renderView($id) {
        $this->template->user = $this->getUser();
        $this->template->usr = $this->userManager->getByID($id);
    }

    public function actionCreate() {
        $this->redirect("Sign:up");
    }
    
    public function actionDelete(){
        $this->flashMessage("Takhle to tady nefunguje","error");
        $this->redirect("Product:default");
    }

    public function renderEdit($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro práci s uživateli musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        } elseif ($this->getUser()->getIdentity()->roles[0] != "admin" && $this->getUser()->getIdentity()->data['id'] != $this->getParameter("id")) {
            $this->flashMessage("Nemůžete zasahovat do ostatních uživatelů", "error");
            $this->redirect("Product:default");
        }
        $this->template->user = $this->getUser();
        $user = $this->userManager->getByID($id);
        $this->template->usr = $user;
        $this['userForm']->setDefaults($user->toArray());
    }

    public function createComponentUserForm() {
        $form = new Form;

        $form->addText('name', 'Jméno: ')->setRequired()->addRule(Form::MAX_LENGTH, "Jméno může obsahovat max. 50 znaků.", 50);
        $form->addText('username', 'Uživatelské jméno: ')->setRequired()->addRule(Form::MAX_LENGTH, "Uživatelské jméno může obsahovat max. 100 znaků.", 100);
        $form->addText('surname', 'Příjmení: ')->setRequired()->addRule(Form::MAX_LENGTH, "Příjmení může obsahovat max. 50 znaků.", 50);
        $form->addEmail('email', 'Email: ')->setRequired()->addRule(Form::MAX_LENGTH, "Email může obsahovat max. 100 znaků.", 100);
        $form->addText('address', 'Adresa: ')->setRequired()->addRule(Form::MAX_LENGTH, "Adresa může obsahovat max. 200 znaků.", 200);
        $form->addSelect('role', 'Role: ', [
            'registered' => 'Registrovaný',
            'productManager' => 'Manažer',
            'admin' => 'Administrátor'
        ])->setAttribute("class", "col-sm-4")->setRequired();
        $form->addUpload('photo', 'Fotka: ')
                ->addRule(Form::IMAGE, "Fotka musí být ve formátu JPEG, PNG nebo GIF.")
                ->addRule(Form::MAX_FILE_SIZE, "Maximální velikost souboru je 16 MB.", 16 * 1024000)
                ->setRequired(FALSE);

        $form->addSubmit('submit', "Upravit")->setAttribute("class", "btn btn-primary");
        $form->onSuccess[] = array($this, 'userFormSucceeded');
        
        Debugger::barDump($form);

        return $form;
    }

    public function userFormSucceeded(Form $form, $data) {
        $id = $this->getParameter("id");
        if ($data['photo']->hasFile()) {
            $data['photo'] = $data['photo']->getContents();
        } else {
            $data['photo'] = $this->userManager->getByID($id)->photo;
        }
        if($data['role'] == NULL){
            $data['role'] = $this->userManager->getByID($id)->role;
        }
        $this->userManager->edit($id, $data);
        $this->flashMessage("Profil byl upraven.", "success");
        $this->redirect("default");
    }

}
