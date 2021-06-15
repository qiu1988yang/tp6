<?php
declare (strict_types=1);

namespace app\test\controller;

use app\model\WsaleOrderModel;
use app\model\WsaleOrderDetailModel;

ini_set("memory_limit", "25688M");

class ImportOrder
{

    public function scanFile($path)
    {
        global $result;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    scanFile($path . '/' . $file);
                } else {
                    $result[] = basename($file);
                }
            }
        }
        return $result;
    }


    public function index()
    {
        $path = 'E:\订单报表';
        $result = $this->scanFile($path);

        foreach ($result as $key => $val) {
            $route = $path . '/' . $val;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($route);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $t = [];
            foreach ($sheetData[1] as $key2 => $val2) {
                $t[$key2] = trim($val2);
            }

            //订单
            $info['brand'] = array_search('店铺名称', $t);
            $info['shop_name'] = array_search('店铺名称', $t);
            $info['order_no'] = array_search('订单编号', $t);
            $info['original_order_no'] = array_search('订单编号', $t);
            $info['order_status'] = array_search('订单状态', $t);
            $info['fare_no'] = array_search('物流单号', $t);
            $info['customer'] = array_search('买家会员名', $t);
            $info['addressee'] = array_search('收货人姓名', $t);
            $info['addressee_phone'] = array_search('联系手机', $t);
            $info['address'] = array_search('收货地址', $t);
            $info['total_price'] = array_search('总金额', $t);
            $info['total_num'] = array_search('宝贝总数量', $t);
            $info['create_time'] = array_search('订单创建时间', $t);
            $info['pay_time'] = array_search('订单付款时间', $t);
            $info['delivery_time'] = array_search('发货时间', $t);
            $info['sys_create_time'] = "系统创建时间";

            //订单详情
            $des['order_no'] = array_search('订单编号', $t);
            $des['sku'] = array_search('宝贝标题', $t);
            $des['goods'] = array_search('宝贝标题', $t);
            $des['num'] = array_search('宝贝总数量', $t);
            $des['price'] = array_search('买家实际支付金额', $t);
            $des['sys_create_time'] = array_search('物流单号', $t);

            $des2 = [];
            $desOrderList = [];
            $info2 = [];
            $orderList = [];
            $res = array();
            foreach ($sheetData AS $key4 => $val3) {
                if ($key4 > 1) {


                    $info2['brand'] = $val3[$info['brand']];
                    $info2['shop_name'] = $val3[$info['shop_name']];
                    $info2['order_no'] = $val3[$info['order_no']];
                    $info2['original_order_no'] = $val3[$info['original_order_no']];
                    $info2['order_status'] = $val3[$info['order_status']];
                    $info2['fare_no'] = $val3[$info['fare_no']];
                    $info2['customer'] = $val3[$info['customer']];
                    $info2['addressee'] = $val3[$info['addressee']];
                    $info2['addressee_phone'] = str_replace("'", '', $val3[$info['addressee_phone']]);
                    $info2['address'] = $val3[$info['address']];
                    $info2['total_price'] = (int)($val3[$info['total_price']] * 100);
                    $info2['total_num'] = $val3[$info['total_num']];
                    $info2['create_time'] = $val3[$info['create_time']];
                    $info2['pay_time'] = $val3[$info['pay_time']];
                    $info2['delivery_time'] = $val3[$info['delivery_time']];
                    $info2['sys_create_time'] = date("Y-m-d H:i:s");
                    $des2['order_no'] = $val3[$des['order_no']];
                    $des2['sku'] = $val3[$des['sku']] ? mb_substr($val3[$des['sku']], 0, 100, 'utf-8') : "";
                    $des2['goods'] = $val3[$des['goods']] ? mb_substr($val3[$des['goods']], 0, 100, 'utf-8') : "";
                    $des2['num'] = $val3[$des['num']];
                    $des2['price'] = (int)($val3[$des['price']] * 100);
                    $des2['sys_create_time'] = date("Y-m-d H:i:s");


                    //查看有没有重复项
                    if (isset($des2['order_no'])) {
                        if (in_array($des2['order_no'], $res)) {
                            dump($des2['order_no']);
                            $desOrderList[] = $des2;
                            unset($info2);  //有：销毁
                        } else {
                            $res[] = $des2['order_no'];
                            $orderList[] = $info2;
                            $desOrderList[] = $des2;
                        }
                    }
                }

            }
            (new WsaleOrderModel())->insertAll($orderList);
            (new WsaleOrderDetailModel())->insertAll($desOrderList);
            exit;
        }

    }


    public function arrayUnset($arr, $key)
    {
        //建立一个目标数组
        $res = array();
        foreach ($arr as $value) {
            //查看有没有重复项
            if (isset($res[$value[$key]])) {
                unset($value[$key]);  //有：销毁
            } else {
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    public function t_w_sale_order()
    {
        $info['渠道'] = "platform";
        $info['品牌'] = "brand";
        $info['店铺代码'] = "shop_code";
        $info['店铺名称'] = "shop_name";
        $info['仓库代码'] = "ware_code";
        $info['仓库名称'] = "ware_name";
        $info['订单编号'] = "order_no";
        $info['退单号'] = "refund_no";
        $info['原单号'] = "original_order_no";
        $info['订单状态'] = "order_status";
        $info['运单号'] = "fare_no";
        $info['物流公司编码'] = "logistics_code";
        $info['物流公司'] = "logistics_name";
        $info['顾客'] = "customer";
        $info['收货人'] = "addressee";
        $info['收货人手机'] = "addressee_phone";
        $info['省'] = "province";
        $info['市'] = "city";
        $info['区'] = "district";
        $info['详细地址'] = "address";
        $info['总额'] = "total_price";
        $info['总数'] = "total_num";
        $info['税额'] = "tax";
        $info['运费'] = "fare";
        $info['订单创建时间'] = "create_time";
        $info['订单支付时间'] = "pay_time";
        $info['订单发货时间'] = "delivery_time";
        $info['系统创建时间'] = "sys_create_time";
        return $info;
    }


    public function t_w_sale_order_detail()
    {
        $info['主表ID'] = "order_id";
        $info['订单号'] = "order_no";
        $info['sku'] = "sku";
        $info['商品'] = "goods";
        $info['数量'] = "num";
        $info['零售价'] = "retail_price";
        $info['支付金额'] = "price";
        $info['系统创建时间'] = "sys_create_time";
        $info['是否更新过分摊价格'] = "is_update";
        return $info;

    }

    public function aaa()
    {
        $info['brand'] = "店铺名称";
        $info['shop_name'] = "店铺名称";
        $info['order_no'] = "订单编码";
        $info['original_order_no'] = "订单编码";
        $info['order_status'] = "订单状态";
        $info['fare_no'] = "运单号";
        $info['customer'] = "顾客";
        $info['addressee'] = "收货人";
        $info['addressee_phone'] = "收货人手机";
        $info['address'] = "详细地址";
        $info['addressee'] = "收货人";
        $info['total_price'] = "总额";
        $info['total_num'] = "总数";
        $info['create_time'] = "订单创建时间";
        $info['pay_time'] = "订单支付时间";
        $info['delivery_time'] = "订单发货时间";
        $info['sys_create_time'] = "系统创建时间";
    }


}
