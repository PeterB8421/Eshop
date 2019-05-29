<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\ProductManager;
use Nette\Application\UI\Form;

final class ProductPresenter extends BasePresenter {

    private $manager;

    function __construct(ProductManager $manager) {
        $this->manager = $manager;
    }

    public function renderDefault($order = 'id'): void {
        $this->template->products = $this->manager->getAll($order);
        \Tracy\Debugger::barDump($this->template->products);
    }
    
    public function renderView($id){
        $this->template->product = $this->manager->getByID($id);
    }
    
    public function renderCreate(){
        
    }
    
    
    public function createComponentCreateForm(){
        $form = new Form;
        
        $form->addText('name')->setRequired()->addRule(Form::MAX_LENGTH,"Název může obsahovat max. 50 znaků.",50);
        $form->addSelect('category', 'Kategorie: ',[
            'tričko'=>'Tričko',
            'mikina'=>'Mikina'
        ])->setRequired();
        $form['category']->setDefaultValue('tričko');
        $form->addTextarea('description','Popis produktu: ')->setAttribute("cols",100)->setAttribute("rows",10);
        $form->addUpload('photo','Fotka produktu: ')->addRule(Form::IMAGE, "Fotka musí být ve formátu JPEG, PNG nebo GIF.")->addRule(Form::MAX_FILE_SIZE,"Maximální velikost souboru je 16 MB.",16*1024);
        $form->addText('price','Cena produktu: ')->addRule(Form::FLOAT)->addRule(Form::MIN,"Minimální cena je 1.00 Kč.",1)->addRule(Form::MAX,"Maximální cena je 999999.99 Kč.",999999)->setRequired();
        
        $form->addSubmit('submit',"Provést");
        
        return $form;
    }

}
