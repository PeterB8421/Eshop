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
        
        if($this->getUser()->getIdentity()->roles[0] == "registered" && $this->getUser()->getIdentity()->data['id'] != $this->orderManager->getByID($id)->user_id){
            $this->flashMessage("Nesmíte prohlížet cizí objednávky","error");
            $this->redirect("Order:default");
        }
        $this->template->order = $this->orderManager->getByID($id);
        $this->template->user = $this->getUser();
    }

    public function renderCreate() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro vytvoření objednávky musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }
        $this->template->user = $this->getUser();
    }

    public function renderEdit($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro úpravu objednávky musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        }
        //Vyřešit zménu objednávek
        elseif ($this->getUser()->getIdentity()->roles[0] == "registered") {
            $this->flashMessage("Pro editaci produktů nemáte dostatečná práva", "error");
            $this->redirect("Product:default");
        }
        $this->template->user = $this->getUser();
        $this->template->userIdentity = $this->getUser()->getIdentity();
        $product = $this->eshopManager->getByID($id);
        $this->template->product = $product;
        $this['productForm']->setDefaults($product->toArray());
    }

    public function handleDelete($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro mazání objednávek musíte být přihlášeni", "warning");
            $this->redirect("Sign:in");
        } elseif ($this->getUser()->getIdentity()->roles[0] == "registered") {
            $this->flashMessage("Pro mazání objednávek nemáte dostatečná práva", "error");
            $this->redirect("Product:default");
        }
        $this->orderManager->deleteRecord($id);
        $this->flashMessage("Produkt úspěšně smazán.", "warning");
    }

    public function createComponentOrderForm() {
        $form = new Form;

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
        $form->addTextarea('note', 'Poznámka k objednávce: ')->setAttribute("rows", 50)->setAttribute("cols", 50);

        $form->addSubmit('submit', "Objednat")->setAttribute("class", "btn btn-primary");
        $form->onSuccess[] = array($this, 'productFormSucceeded');

        return $form;
    }

    public function productFormSucceeded(Form $form, $data) {
        $id = $this->getParameter("id");
        $this->orderManager->updateRecord($id, $data);
        $this->flashMessage("Objednávka byla upravena.", "success");
        $this->redirect("default");
    }

}
