<?php

namespace App\Presenters;

use Nette;
use App\Presenters\BasePresenter;
use App\CoreModule\Model\ProductsManager;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Tracy\Debugger;

Debugger::enable();


final class HomepagePresenter extends BasePresenter
{
    private $productsManager;

    public function __construct(ProductsManager $productsManager)
    {
        parent::__construct();
        $this->productsManager = $productsManager;
    }

    /**
     * Creates Serial Number Form (Step 1)
     */
    protected function createComponentSerialNumberForm() 
    {
        $form = new Form;
        $form->addText('serial_number', 'Serial Number')
            ->setRequired()
            ->setHtmlAttribute('id', 'serial-number')
            ->setHtmlAttribute('autofocus', true)
            ->setHtmlAttribute('placeholder', '000000000-0-0000')
            ->addRule(Form::PATTERN, 'message', '.*[0-9]\-');

        $form->addSubmit('verify_light', 'Verify The Light Object');
        $form->onValidate[] = [$this, 'verifySerialNumber'];

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'dl';
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        $this->template->serial_number = false;
        $this->template->success = false;

        return $form;
    }

    /**
     * Serial Number verification action
     */
    public function verifySerialNumber($form) 
    {
        $values = $form->getValues();

        $verification = $this->getSession('verification');
        unset($verification->serial_number);
        $verification->serial_number = $values->serial_number;

        $this->template->serial_number = $verification->serial_number;
        
        $owner = $this->productsManager->getProduct($verification->serial_number);

        if ($owner && $owner->email) {
            $this->template->success = true;
        }  
    }

    /** 
     * Verify The Owner Form with single button
     */
    protected function createComponentRequestSecretForm()
    {
        $form = new Form;
        $form->addSubmit('verify_ownership', 'Verify The Ownership');
        $form->onSubmit[] = [$this, 'verifyOwnership'];

        return $form;
    }

    /**
     * Sends secret code to registered email address for further verification.
     */
    public function verifyOwnership() 
    {
        // get serial
        $verification = $this->getSession('verification');
        // get owner
        $owner = $this->productsManager->getProduct($verification->serial_number);
        // send email
        // @todo
        // redirect to verification form
        $this->redirect('Verification:');
    }

    function renderDefault() 
    {
        $form = $this['serialNumberForm'];
    }
}
