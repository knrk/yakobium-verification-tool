<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;

/**
 * Základní presenter pro všechny ostatní presentery aplikace.
 * @package App\Presenters
 */
abstract class BasePresenter extends Presenter
{
    public function beforeRender() 
    {
        parent::beforeRender();
        $this->template->google_ua = $this->context->parameters['google']['ua'];
    }
}
?>