<?php
declare (strict_types=1);

namespace app\test\controller;

use app\model\WsaleOrderModel;
use app\model\WsaleOrderDetailModel;
use think\facade\Db;
use app\common\lib\kuaidi\Kuaidis;

ini_set("memory_limit", "25688M");

class Kuaidi
{

    public function index()
    {
        $list = Db::name('test')->where(['status' => 1000])->group('wu_danhao,wu_gongsi')->limit(10000)->select();
        foreach ($list as $key => $val) {
            $expressMap = Db::name('w_express')->column('code', 'name');
           /* if($val['wu_gongsi']=='德邦快递1'){
                $val['wu_gongsi'] = '德邦快递';
            }*/
            $wu_gongsi = $expressMap[$val['wuliugongsi']] ?? "";




            $wu_danhao = $val['wu_danhao'];
            $getExpressInfo = (new Kuaidis())->getExpressInfo($wu_gongsi, $wu_danhao);
            $statusKd = (new Kuaidis())->statusKd();
            if (!empty($getExpressInfo['status'])) {
                if ($getExpressInfo['status'] == 200) {
                    $kuadi_gunji = [];
                    $kuadi_gunji22 = [];
                    foreach ($getExpressInfo['data'] as $key2 => $val2) {
                        $kuadi_gunji['chuku_no'] = $val['chuku_no'];
                        $kuadi_gunji['fasheng_time'] = $val2['time'];
                        $kuadi_gunji['content'] = $val2['context'];
                        $kuadi_gunji['address'] = "";

                        if(strstr($val2['context'], '揽收')){
                            $kuadi_gunji['status'] = "揽收";
                        }elseif(strstr($val2['context'], '疑难')){
                            $kuadi_gunji['status'] = "疑难";
                        }elseif(strstr($val2['context'], '签收')){
                            $kuadi_gunji['status'] = "签收";
                        }elseif(strstr($val2['context'], '派件')){
                            $kuadi_gunji['status'] = "派件";
                        }elseif(strstr($val2['context'], '退回')){
                            $kuadi_gunji['status'] = "退回";
                        }elseif(strstr($val2['context'], '转单')){
                            $kuadi_gunji['status'] = "转单";
                        }elseif(strstr($val2['context'], '待清关')){
                            $kuadi_gunji['status'] = "待清关";
                        }elseif(strstr($val2['context'], '已清关')){
                            $kuadi_gunji['status'] = "已清关";
                        }elseif(strstr($val2['context'], '清关异常')){
                            $kuadi_gunji['status'] = "清关异常";
                        }elseif(strstr($val2['context'], '拒签')){
                            $kuadi_gunji['status'] = "拒签";
                        }elseif(strstr($val2['context'], '收件')){
                            $kuadi_gunji['status'] = "收件";
                        }else{
                            $kuadi_gunji['status'] = "在途";
                        }
                        $kuadi_gunji['wu_gongsi'] = $val['wu_gongsi'];
                        $kuadi_gunji['wu_danhao'] = $val['wu_danhao'];
                        $kuadi_gunji22[] = $kuadi_gunji;
                    }
                    Db::name('kuadi_gunji')->insertAll($kuadi_gunji22);
                    unset($kuadi_gunji22);
                    $msg = $statusKd[$getExpressInfo['state']];

                    Db::name('test')->where(['wu_danhao' => $wu_danhao, 'wu_gongsi' => $val['wu_gongsi']])->update(['status' => $getExpressInfo['state'], 'msg' => $msg]);
                }
            } else {
                Db::name('test')->where(['wu_danhao' => $wu_danhao, 'wu_gongsi' => $val['wu_gongsi']])->update(['status' => 100, 'msg' => $getExpressInfo['message']]);
            }
        }
    }
}
