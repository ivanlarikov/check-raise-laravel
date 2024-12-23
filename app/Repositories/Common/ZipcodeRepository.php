<?php

namespace App\Repositories\Common;

use App\Models\Common\Zipcode;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ZipcodeRepository extends BaseRepository
{
    /**
     * @var Tournament
     */
    protected Zipcode $zipcode;

    /**
     * @param Zipcode $zipcode
     */
    public function __construct(Zipcode $zipcode)
    {
        $this->zipcode = $zipcode;
        parent::__construct($zipcode);
    }
    
}
