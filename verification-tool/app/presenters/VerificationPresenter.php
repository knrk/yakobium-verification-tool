<?php

namespace App\Presenters;

use Nette;
// use App\CoreModule\Model\ProductsManager;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Http\Session;

use Tracy\Debugger;
Debugger::enable();


class VerificationPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        $verification = $this->getSession('verification');
        if (!isset($verification->serial_number)) {
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentHashForm() 
    {
        $form = new Form;
        $form->addText('hash_power', 'Enter the unique code sent to the owner of the light object')
            ->setRequired()
            ->setHtmlAttribute('id', 'secret-hash')
            ->setHtmlAttribute('autofocus', true);
        $form->addSubmit('verify_owner', 'Verify The Owner');
        $form->onValidate[] = [$this, 'verificationProcess'];

        $verification = $this->getSession('verification');
        $this->template->serial_number = $verification->serial_number;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'dl';
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';
        return $form;
    }

    public function verificationProcess()
    {
        // validate entered secret code and show result.
    }

    function renderDefault() 
    {
        $form = $this['hashForm'];
    }
}
