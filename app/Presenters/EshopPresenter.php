<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\EshopManager;

final class EshopPresenter extends BasePresenter {

    private $manager;

    function __construct(EshopManager $manager) {
        $this->manager = $manager;
    }

    public function renderDefault($order = 'id'): void {
        $this->template->products = $this->manager->getAll($order);
        \Tracy\Debugger::barDump($this->template->products);
    }

}
