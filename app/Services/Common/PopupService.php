<?php
namespace App\Services\Common;

use App\Repositories\Common\PopupRepository;
use App\Services\BaseService;
use App\Models\Common\Popup;

class PopupService extends BaseService
{
    /**
     * @var PopupRepository
     */
    protected PopupRepository $popup;

    /**
     * @param PopupRepository $popup
     */
    public function __construct(PopupRepository $popup)
    {
        $this->popup = $popup;
        parent::__construct($this->popup);
    }

}
