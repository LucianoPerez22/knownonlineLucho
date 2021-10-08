<?php

namespace App\Http\Controllers;

use App\Items;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrdersController extends Controller
{
    public function index($dateStart = null, $dateEnd = null)
    {
        ( $dateStart === null ) ? $dateStart = date('Y-m-d', strtotime($dateStart." - 6 month")) : '';
        ( $dateEnd === null ) ? $dateEnd = date('Y-m-d') : '';

        $data = $this->buildData($dateStart, $dateEnd);

        $result = array_keys($data);

        for ($i=0; $i < count($result) ; $i++) {
            $orders = new Orders();
            $items = new Items();

            $orders->orderId    = $data[$result[$i]]['order']['orderId'];
            $orders->firstName  = $data[$result[$i]]['order']['firstName'];
            $orders->lastName   = $data[$result[$i]]['order']['lastName'];
            $orders->email      = $data[$result[$i]]['order']['email'];
            $orders->value      = $data[$result[$i]]['order']['totalValue'];
            $orders->processed  = 0;
            $orders->save();

            $key_data = $data[$result[$i]]['order']['orderId'];

            foreach($data[$key_data]['items'] as $keyItems => $valueItems){
                    $items->orderId     = $orders->id;
                    $items->name        = $valueItems['Name'];
                    $items->productId   = $valueItems['productId'];
                    ($valueItems['refId'] === null) ? $valueItems['refId'] = "null" : '';
                    $items->refId       = $valueItems['refId'];
                    $items->quantity    = $valueItems['quantity'];
                    $items->save();
            }
        }
    }

    public function getAllOrders($dateStart, $dateEnd)
    {
        $response_orders = Http::withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-VTEX-API-AppKey" => "vtexappkey-knownonline-IPWBFW",
            'X-VTEX-API-AppToken' => 'CVBSREIACFNEEBYQWRZZEGPEJJMYKTFKZUBGQDIAZICUEGRPXZZYKLWVFWJHSKQJZCFJASASIZAVEUACSWAKTGAOYGATUBIPSTVCBHPFZHPLKBRKWGOVJFPSBQLTRGXH',

        ])->get('https://knownonline.vtexcommercestable.com.br/api/oms/pvt/orders?f_creationDate=creationDate%3A%5B' . $dateStart .'T02%3A00%3A00.000Z%20TO%20' . $dateEnd  . 'T01%3A59%3A59.999Z%5D&f_hasInputInvoice=false&orderBy=creationDate,desc&per_page=2000');

        $data = json_decode($response_orders);
        $info = $data->list;

        return $info;
    }

    public function getOrder($orderId)
    {
        $uri = "https://knownonline.vtexcommercestable.com.br/api/oms/pvt/orders/". $orderId;

        $response_order = Http::withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-VTEX-API-AppKey" => "vtexappkey-knownonline-IPWBFW",
            'X-VTEX-API-AppToken' => 'CVBSREIACFNEEBYQWRZZEGPEJJMYKTFKZUBGQDIAZICUEGRPXZZYKLWVFWJHSKQJZCFJASASIZAVEUACSWAKTGAOYGATUBIPSTVCBHPFZHPLKBRKWGOVJFPSBQLTRGXH',
        ])->get($uri);

        $info_order = json_decode($response_order);
        return $info_order;
    }

    public function buildData($dateStart, $dateEnd)
    {
        $info = $this->getAllOrders($dateStart, $dateEnd);
        if (count($info) != 0)
        {
            $arrReturn=array();

            foreach ($info as $key => $value)
            {
                if ($value->status == "ready-for-handling")
                {
                    $info_order = $this->getOrder($value->orderId);

                    $client     = $info_order->clientProfileData;
                    $payment    = $info_order->paymentData->transactions;
                    $items      = $info_order->items;

                    foreach ($payment as $key_pays => $value_pays)
                    {
                        $pays = ($value_pays->payments);

                        foreach ($pays as $key_pay => $value_pay)
                        {
                            $valuepay = $value_pay->id;
                        }
                    }

                    $arrReturn[$value->orderId]['order'] = [
                        "date"          => date('Y-m-d',strtotime($value->creationDate)),
                        "orderId"       => $value->orderId,
                        "firstName"     => $client->firstName,
                        "lastName"      => $client->lastName,
                        "email"         => $client->email,
                        "idPay"         => $valuepay,
                        "totalValue"    => $value->totalValue
                    ];

                    foreach ($items as $key_items => $value_items)
                    {
                        $arrReturn[$value->orderId]['items'][$value_items->productId] = [
                            "orderId"    => $value->orderId,
                            "Name"       => $value_items->name,
                            "refId"      => $value_items->refId,
                            "quantity"   => $value_items->quantity,
                            "productId"  => $value_items->productId,

                        ];
                    }
                }
            }
           return $arrReturn;
        }else{
            return print("No hay registros para mostrar");
        }
    }
}
