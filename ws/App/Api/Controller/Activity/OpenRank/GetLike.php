<?php
namespace App\Api\Controller\Activity\OpenRank;
use App\Api\Service\Module\OpenRankService;
use App\Api\Controller\BaseController;

class GetLike extends BaseController
{

    public function index()
    {
        $this->sendMsg( [
            'world'      => OpenRankService::getInstance()->getLikeRankFmtData($this->player),
        ] );
    }

}