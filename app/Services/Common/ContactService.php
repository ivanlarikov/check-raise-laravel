<?php
namespace App\Services\Common;

use App\Repositories\Common\ContactRepository;
use App\Services\BaseService;
use App\Models\Common\Contact;

class ContactService extends BaseService
{
    /**
     * @var ContactRepository
     */
    protected ContactRepository $contact;

    /**
     * @param ContactRepository $user
     */
    public function __construct(ContactRepository $contact)
    {
        $this->contact = $contact;
        parent::__construct($this->contact);
    }

}
