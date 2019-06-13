<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\ProductManager;
use App\Model\UserManager;
use App\Model\OrderManager;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class OrderPresenter extends BasePresenter {

    private $eshopManager;
    private $userManager;
    private $orderManager;

    function __construct(ProductManager $manager, \App\Model\UserManager $usrMgr, OrderManager $orderMgr) {
        $this->eshopManager = $manager;
        $this->userManager = $usrMgr;
        $this->orderManager = $orderMgr;
    }

    public function renderDefault($order = 'id'): void {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro prohlížení objednávek musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }

        if ($this->getUser()->getIdentity()->roles[0] != "registered") {
            $this->template->orders = $this->orderManager->getAll($order);
        } else {
            $this->template->orders = $this->orderManager->getAllByUser($this->getUser()->getIdentity()->data['id'], $order);
        }
        $this->template->user = $this->getUser();
        \Tracy\Debugger::barDump($this->template->user);
    }

    public function renderView($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro prohlížení objednávek musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }

        if ($this->getUser()->getIdentity()->roles[0] == "registered" && $this->getUser()->getIdentity()->data['id'] != $this->orderManager->getByID($id)->user_id) {
            $this->flashMessage("Nesmíte prohlížet cizí objednávky", "error");
            $this->redirect("Order:default");
        }
        $this->template->order = $this->orderManager->getByID($id);
        $this->template->user = $this->getUser();
    }

    public function renderCreate($pr_id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro vytvoření objednávky musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }
        $this->template->user = $this->getUser();
        $this->template->product = $this->eshopManager->getByID($pr_id);
    }

    public function renderEdit($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro úpravu objednávky musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }
        $this->template->user = $this->getUser();
        $this->template->order = $this->orderManager->getByID($id);
        $this['orderForm']->setDefaults($this->orderManager->getByID($id)->toArray());
    }

    public function handleDelete($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro mazání objednávek musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        } elseif ($this->getUser()->getIdentity()->roles[0] == "registered" && $this->orderManager->getByID($id)->user_id != $this->getUser()->getIdentity()->data['id']) {
            $this->flashMessage("Pro mazání objednávek nemáte dostatečná práva", "error");
            $this->redirect("Product:default");
        }
        $this->orderManager->deleteRecord($id);
        $this->flashMessage("Objednávka úspěšně smazána.", "warning");
    }

    public function createComponentOrderForm() {
        $form = new Form;

        $form->addInteger('product_id')->setAttribute("hidden");
        $form->addInteger('user_id')->setAttribute("hidden");
        $form->addInteger('quantity', 'Počet kusů: ')->addRule(Form::RANGE, "Můžete objednat 0 - 99 kusů", [0, 99])->setRequired();
        $form->addSelect('payType', 'Typ platby: ', [
            'kreditní karta' => 'Kreditní kartou (online)',
            'převodem' => 'Bankovní převod',
            'hotově/dobírkou' => 'Hotově nebo dobírkou',
            'PayPal' => 'PayPal'
        ])->setAttribute("class", "col-sm-4")->setRequired();
        $form->addSelect('deliveryType', 'Typ dodání: ', [
            'osobní odběr' => 'Osobní odběr',
            'Česká pošta' => 'Česká pošta',
            'PPL' => 'PPL'
        ])->setAttribute("class", "col-sm-4")->setRequired();
        $form->addTextarea('note', 'Poznámka k objednávce: ')->setAttribute("rows", 5)->setAttribute("cols", 50);
        $form->addSelect('status', 'Stav objednávky: ', [
            'processing' => 'Zpracování',
            'accepted' => 'Přijata',
            'delivering' => 'Odesláno',
            'completed' => 'Dokončeno'
        ]);

        $form->addSubmit('submit', "Objednat")->setAttribute("class", "btn btn-primary");
        $form->onSuccess[] = array($this, 'orderFormSucceeded');

        return $form;
    }

    public function orderFormSucceeded(Form $form, $data) {
        $id = $this->getParameter("id");
        if ($id) {
            $data['user_id'] = $this->orderManager->getByID($id)->user_id;
            $this->orderManager->updateRecord($id, $data);
            $this->flashMessage("Objednávka byla upravena.", "success");
        } else {
            $this->orderManager->createRecord($data);
            $this->flashMessage("Objednávka vytvořena", "success");
        }
        $this->redirect("default");
    }

}
