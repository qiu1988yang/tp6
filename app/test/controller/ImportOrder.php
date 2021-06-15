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
        $path = 'E:\5.27~6.7\备份';
        $result = $this->scanFile($path);

        foreach ($result as $key => $val) {
            dump($val);
            $route = $path . '/' . $val;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($route);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);


            file_put_contents("sites.txt", "$val" . "---" . count($sheetData), FILE_APPEND);
            $t = [];
            foreach ($sheetData[1] as $key2 => $val2) {
                if($val2){
                    $t[$key2] = trim($val2);
                }
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
                    if (isset($val3[$info['address']])) {
                        $encode = mb_detect_encoding($val3[$info['address']], array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                        if ($encode == 'UTF-8') {
                            $info2['address'] = $val3[$info['address']] ? mb_substr($val3[$info['address']], 0, 100, 'utf-8') : "";
                            $info2['address'] = $this->remove_emoji($info2['address']);
                        } else {
                            file_put_contents("sites2.txt", "$val" . "---" .  $val3[$info['address']], FILE_APPEND);
                            $info2['address'] = "";
                        }
                    } else {
                        $info2['address'] = "";
                    }
                    $info2['address'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD",  $info2['address']);
                    $info2['address'] = str_replace('�', '', $info2['address']) ;
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
                            //dump($des2['order_no']);
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
            $orderList11 = array_chunk($orderList, 1000);
            $desOrderList22 = array_chunk($desOrderList, 1000);
            foreach ($orderList11 AS $key11 => $val11) {
                (new WsaleOrderModel())->insertAll($val11);
            }
            foreach ($desOrderList22 AS $key22 => $val22) {
                (new WsaleOrderDetailModel())->insertAll($val22);
            }

        }

    }



    public function FilterPartialUTF8Char($str)

    {
        $str = preg_replace("/[\\xC0-\\xDF](?=[\\x00-\\x7F\\xC0-\\xDF\\xE0-\\xEF\\xF0-\\xF7]|$)/", "", $str);

        $str = preg_replace("/[\\xE0-\\xEF][\\x80-\\xBF]{0,1}(?=[\\x00-\\x7F\\xC0-\\xDF\\xE0-\\xEF\\xF0-\\xF7]|$)/", "", $str);

        $str = preg_replace("/[\\xF0-\\xF7][\\x80-\\xBF]{0,2}(?=[\\x00-\\x7F\\xC0-\\xDF\\xE0-\\xEF\\xF0-\\xF7]|$)/", "", $str);

        return $str;

    }


    public function remove_emoji($string) {

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
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
