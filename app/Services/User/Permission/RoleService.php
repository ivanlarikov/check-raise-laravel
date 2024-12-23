<?php

namespace App\Services\User\Permission;

use App\Repositories\User\Permission\RoleRepository;
use App\Services\BaseService;

class RoleService extends BaseService
{
    /**
     * @var RoleRepository
     */
    protected RoleRepository $role;

    /**
     * @param RoleRepository $role
     */
    public function __construct(RoleRepository $role)
    {
        $this->role = $role;
        parent::__construct($this->role);
    }
}
