<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\ProductManager;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class ProductPresenter extends BasePresenter {

    private $manager;

    function __construct(ProductManager $manager) {
        $this->manager = $manager;
    }

    public function renderDefault($order = 'id'): void {
        $this->template->products = $this->manager->getAll($order);
        \Tracy\Debugger::barDump($this->template->products);
    }

    public function renderView($id) {
        $this->template->product = $this->manager->getByID($id);
    }

    public function renderCreate() {
        
    }
    
    public function renderEdit($id){
        $product = $this->manager->getByID($id);
        $this->template->product = $product;
        $this['productForm']->setDefaults($product->toArray());
    }

        public function handleDelete($id){
        $this->manager->deleteRecord($id);
        $this->flashMessage("Produkt úspěšně smazán.","warning");
        //$this->redraw("vypis");
    }

    public function createComponentProductForm() {
        $form = new Form;

        $form->addText('name', 'Název produktu: ')->setRequired()->addRule(Form::MAX_LENGTH, "Název může obsahovat max. 50 znaků.", 50);
        $form->addSelect('category', 'Kategorie: ', [
            'tričko' => 'Tričko',
            'mikina' => 'Mikina'
        ])->setAttribute("class", "col-sm-4")->setRequired();
        $form['category']->setDefaultValue('tričko');
        $form->addTextarea('description', 'Popis produktu: ')->setAttribute("cols", 100)->setAttribute("rows", 10);
        $form->addUpload('photo', 'Fotka produktu: ')
                ->addRule(Form::IMAGE, "Fotka musí být ve formátu JPEG, PNG nebo GIF.")
                ->addRule(Form::MAX_FILE_SIZE, "Maximální velikost souboru je 16 MB.", 16 * 1024000)
                ->setRequired(FALSE);
        $form->addText('price', 'Cena produktu: ')->addRule(Form::FLOAT)->addRule(Form::MIN, "Minimální cena je 1.00 Kč.", 1)->addRule(Form::MAX, "Maximální cena je 999999.99 Kč.", 999999)->setRequired();

        $form->addSubmit('submit', "Provést")->setAttribute("class", "btn btn-primary");
        $form->onSuccess[] = array($this, 'productFormSucceeded');

        return $form;
    }

    public function productFormSucceeded(Form $form, $data) {
        Debugger::barDump($data['photo']->getContents());
        $id = $this->getParameter("id");
        if (!$id) {
            if ($data['photo']->hasFile()) {
                $data['photo'] = $data['photo']->getContents();
            } else {
                $data['photo'] = NULL;
            }
            $this->manager->createRecord($data);
            $this->flashMessage("Produkt byl vytvořen.", "success");
        }
        else{
            if ($data['photo']->hasFile()) {
                $data['photo'] = $data['photo']->getContents();
            } else {
                $data['photo'] = $this->manager->getByID($id)->photo;
            }
            $this->manager->updateRecord($id, $data);
            $this->flashMessage("Produkt byl upraven.", "success");
        }
        $this->redirect("default");
    }

}
