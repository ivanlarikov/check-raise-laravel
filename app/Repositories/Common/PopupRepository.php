<?php

namespace App\Repositories\Common;

use App\Models\Common\Popup;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PopupRepository extends BaseRepository
{
    /**
     * @var Popup
     */
    protected Popup $popup;

    /**
     * @param Popup $zipcode
     */
    public function __construct(Popup $popup)
    {
        $this->popup = $popup;
        parent::__construct($popup);
    }
    
}
