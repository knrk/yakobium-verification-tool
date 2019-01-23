<?php

namespace App\Presenters;

use App\Model\HashManager;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Security\Passwords;

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
        $form->addText('secret_hash', 'Enter the unique code sent to the owner of the light object')
            ->setRequired()
            ->setHtmlAttribute('id', 'secret-hash')
            ->setHtmlAttribute('autofocus', true);
        $form->addSubmit('verify_owner', 'Verify The Owner');
        $form->onValidate[] = [$this, 'verificationProcess'];

        $verification = $this->getSession('verification');
        $this->template->serial_number = $verification->serial_number;
        $this->template->success = false;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'dl';
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';
        return $form;
    }

    public function verificationProcess($form)
    {
        // validate entered secret code and show result.
        $values = $form->getValues();
        $secret = $values->secret_hash;

        $verification = $this->getSession('verification');

        $hash = new HashManager;
        if ($secret == $hash->tokenize($hash->encode($verification->owner_email))) {
            $this->template->success = true;
        } 

        // @todo 

    }

    function renderDefault() 
    {
        $form = $this['hashForm'];
    }
}
