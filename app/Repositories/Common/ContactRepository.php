<?php

namespace App\Repositories\Common;

use App\Models\Common\Contact;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ContactRepository extends BaseRepository
{
    /**
     * @var Tournament
     */
    protected Contact $contact;

    /**
     * @param Contact $contact
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
        parent::__construct($contact);
    }
    
}
