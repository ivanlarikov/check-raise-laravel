<?php
namespace App\Services\Common;

use App\Repositories\Common\ZipcodeRepository;
use App\Services\BaseService;
use App\Models\Common\Zipcode;

class ZipcodeService extends BaseService
{
    /**
     * @var ZipcodeRepository
     */
    protected ZipcodeRepository $zipcode;

    /**
     * @param ZipcodeRepository $zipcode
     */
    public function __construct(ZipcodeRepository $zipcode)
    {
        $this->zipcode = $zipcode;
        parent::__construct($this->zipcode);
    }

}
