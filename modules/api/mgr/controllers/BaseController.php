<?php

namespace app\modules\api\mgr\controllers;

use app\lib\bl\session\CompanySession;
use app\lib\common\controller\ApiBaseController;

abstract class BaseController extends ApiBaseController
{
    protected $curCompany = null;
    protected $curCompanyID = null;
    public function __construct($id, $module, $config = [])
    {
        if (CompanySession::exist())
        {
            $this->curCompany = CompanySession::get();
            $this->curCompanyID = $this->curCompany['id'];
        }

        parent::__construct($id, $module, $config);
    }
}