<?php
namespace App\Api\Validate;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Controller\Paradise\DailyReset;
use App\Api\Controller\Paradise\WeekReset;
use App\Api\Controller\Paradise\Self\Get as SelfGet;
use App\Api\Controller\Paradise\Self\CollectGoods as SelfCollectGoods;
use App\Api\Controller\Paradise\Self\WorkerAdd as SelfWorkerAdd;
use App\Api\Controller\Paradise\Self\RefreshGoods as SelfRefreshGoods;
use App\Api\Controller\Paradise\Self\AdRefreshGoods as SelfAdRefreshGoods;
use App\Api\Controller\Paradise\RecallWorker;

use App\Api\Controller\Paradise\Around\Get as AroundGet;
use App\Api\Controller\Paradise\Around\Info as AroundInfo;
use App\Api\Controller\Paradise\Around\Refresh as AroundRefresh;
use App\Api\Controller\Paradise\Around\CollectGoods as AroundCollectGoods;
use App\Api\Controller\Paradise\Around\ExitRoom as AroundExitRoom;

use App\Api\Controller\Paradise\Notice\GetAdmintInfo as NoticeGetAdminInfo;
use App\Api\Controller\Paradise\Notice\GetAdmintDetail as NoticeGetAdminDetail;
use App\Api\Controller\Paradise\Notice\GetAdminGoodsDetail as NoticeGetAdminGoodsDetail;
use App\Api\Controller\Paradise\Notice\StartCollect as NoticeStartCollect;
use App\Api\Controller\Paradise\Notice\ModifyCollect as NoticeModifyCollect;
use App\Api\Controller\Paradise\Notice\CollectSuccess as NoticeCollectSuccess;
use App\Api\Controller\Paradise\Notice\RecallWorker as NoticeRecallWorker;
use App\Api\Controller\Paradise\Notice\GetWorkerTask as NoticeGetWorkerTask;
use App\Api\Controller\Paradise\Notice\ExitRoom as NoticeExitRoom;

class MapClass
{
    use CoroutineSingleTon;

    private $classs = array(
        "getSelfParadiseInfo"                   => SelfGet::class,
        "paradiseCollectSelfGoods"              => SelfCollectGoods::class,
        "workerAdd"                             => SelfWorkerAdd::class,
        "refreshGoods"                          => SelfRefreshGoods::class,
        "adRefreshGoods"                        => SelfAdRefreshGoods::class,
        "recallWorker"                          => RecallWorker::class,

        "getAroundParadiseList"                 => AroundGet::class,
        "getAroundParadiseInfo"                 => AroundInfo::class,
        "refreshParadiseAround"                 => AroundRefresh::class,
        "exitRoom"                              => AroundExitRoom::class,
        "aroundCollectGoods"                    => AroundCollectGoods::class,
        
        "dailyReset"                            => DailyReset::class,
        "weekReset"                             => WeekReset::class,
        
        //其他actor发送的为大写字母
        "NoticeGetAdminInfo"                    => NoticeGetAdminInfo::class,
        "NoticeGetAdminDetail"                  => NoticeGetAdminDetail::class,
        "NoticeGetAdminGoodsDetail"             => NoticeGetAdminGoodsDetail::class,
        "NoticeStartCollect"                    => NoticeStartCollect::class,
        "NoticeModifyCollect"                   => NoticeModifyCollect::class,
        "NoticeCollectSuccess"                  => NoticeCollectSuccess::class,
        "NoticeRecallWorker"                    => NoticeRecallWorker::class,
        "NoticeGetWorkerTask"                   => NoticeGetWorkerTask::class,
        "NoticeExitRoom"                        => NoticeExitRoom::class,
    );

    public function getClassPath(string $method):string
    {
        return array_key_exists($method,$this->classs)? $this->classs[$method] : '';
    }
}
