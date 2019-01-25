<?php

namespace App\Presenters;

use App\Presenters\BasePresenter;
use App\CoreModule\Model\ProductsManager;
use App\Model\HashManager;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\DateTime;
use Tracy\Debugger;

Debugger::enable();


final class HomepagePresenter extends BasePresenter
{
    private $productsManager;
    private $hashManager;
    private $requests_limit = 3;
    private $requests_expire = '+1 minute';

    private $mailer;

    public function __construct(ProductsManager $productsManager, IMailer $mailer)
    {
        parent::__construct();
        $this->productsManager = $productsManager;
        $this->mailer = $mailer;
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
            ->addRule(Form::PATTERN, 'message', '(.*[0-9]{6,9})\-[0-9]{1}\-([0-9]{4})');

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

        if ($verification->trials_timestamp) {
            $last_request_timestamp = DateTime::from($verification->trials_timestamp);
            $last_request_timestamp->modify($this->requests_expire);

            $current_request_timestamp = new DateTime;

            if ($last_request_timestamp->getTimestamp() < $current_request_timestamp->getTimestamp()) {
                unset($verification->trials);
                $verification->trials = 0;
                unset($verification->trials_timestamp);
                unset($verification->trials_limit_reached);
            }
        }

        $this->template->serial_number = $verification->serial_number;
        
        $light = $this->productsManager->getProduct($verification->serial_number);
        if ($light) {
            $owner = $this->productsManager->getOwner($this->productsManager->getOwnerId($light->id));
            $verification->owner_id = $owner->id;
            $verification->owner_email = $owner->email;
            
            if ($owner && $owner->email) {
                $this->template->success = true;
                if ($verification->trials === $this->requests_limit) {
                    $this->template->trials_limit_reached = true;
                }
            }  
        }
    }

    /** 
     * Verify The Owner Form with single button
     */
    protected function createComponentRequestSecretForm()
    {
        $verification = $this->getSession('verification');
        $form = new Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = 'div';

        if ($verification->trials < $this->requests_limit) {
            $form->addSubmit('verify_ownership', 'Verify The Ownership');
        }
        $form->onSubmit[] = [$this, 'verifyOwnership'];

        return $form;
    }

    /**
     * Sends secret code to registered email address for further verification.
     */
    public function verifyOwnership() 
    {
        $verification = $this->getSession('verification');
        $owner = $this->productsManager->getOwner($verification->owner_id);

        try {
            // send email
            $template = $this->createTemplate();
            $template->setFile(__DIR__ . '/templates/Emails/email.latte');
            $hash = new HashManager;
            $template->hash = $hash->tokenize($hash->encode($owner->email));

            $message = new Message;
            $message->setSubject('Yakobium Light Object - Secret Hash')
                    ->setFrom('robot@yakobium.com')
                    ->addTo($owner->email)
                    ->setHtmlBody($template);

            $this->mailer->send($message);

            !isset($verification->trials) ? $verification->trials = 0 : $verification->trials++;

            if ($verification->trials === $this->requests_limit) {
                $verification->trials_timestamp = new DateTime;    
            }

            $this->redirect('Verification:');

        } catch (SendException $e) {
            // @todo error was logged, but we need to say user that there was some problem            
            Debugger::log($e);
        }
    }

    function renderDefault() 
    {
        $form = $this['serialNumberForm'];
    }
}
