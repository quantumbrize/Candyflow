<?php

namespace App\Controllers\Api;

use App\Controllers\Main_Controller;
use App\Controllers\Api\Product_Controller;
use App\Models\OrdersModel;
use App\Models\OrderItemsModel;
use App\Models\PaymentsModel;
use App\Models\CommonModel;
use App\Models\AddressModel;
use App\Models\UsersModel;
use App\Models\UserCartModel;
use App\Models\OrdersCancelledModel;
use App\Models\OrdersReturnModel;
use App\Models\VendorModel;
use App\Models\ProductSizeListModel;
use App\Models\ItemStocksModel;
use App\Models\VendorWalletModel;
use App\Models\VendorWalletHistoryModel;

class Order_Controller extends Main_Controller
{
    public function index(): void
    {
        echo 'Order_Controller';
    }



    private function order_confirm($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Cannot Place Order',
            'data' => []
        ];

        try {
            // Decode JSON data
            $data = json_decode(json_encode($data), true);

            // Initialize models
            $OrdersModel = new OrdersModel();
            $OrderItemsModel = new OrderItemsModel();
            $PaymentsModel = new PaymentsModel();
            $ItemStocksModel = new ItemStocksModel();

            // $this->prd($data);

            if (!empty($data['user_data']['address'])) {
                date_default_timezone_set('Asia/Kolkata');
                $orderData = [
                    "uid" => $this->generate_uid(UID_ORDERS),
                    "user_id" => $data['user_data']['user']['uid'],
                    "shipping_address_id" => $data['user_data']['address']['uid'],
                    "shipping_method" => "free",
                    "user_name" => $data['user_data']['name'],
                    "phone_number" => $data['user_data']['number'],
                    "email" => $data['user_data']['email'],
                    "order_discount_amount" => 0,
                    "order_discount_percentage" => 0,
                    "sub_total" => $data['user_cart']['subTotal'],
                    "total" => $data['user_cart']['total'],
                    "payment_type" => 'cod',
                    "created_at" => date('Y-m-d H:i:s'),
                ];
                $OrdersIsSaved = $OrdersModel->insert($orderData);

                // Insert payment data
                $paymentData = [
                    "uid" => $this->generate_uid(UID_PAYMENTS),
                    "order_id" => $orderData['uid'],
                    "type" => $data['payment_data']['method']
                ];
                $PaymentsIsSaved = $PaymentsModel->insert($paymentData);

                // Insert order items
                $OrderItemsIsSaved = true;
                // Initialize an array to hold Shiprocket order items
                $shiprocketOrderItems = [];

                foreach ($data['user_cart']['cart'] as $cart) {
                    $discountAmount = $this->calculateDiscount($cart['product']['base_price'], $cart['product']['base_discount']);
                    $priceAfterDiscount = $cart['product']['base_price'] - $discountAmount;
                    $res = $this->add_vendor_commission($cart['product']);
                    // $this->prd($res);
                    // Prepare Order Items data
                    $OrderItemsData = [
                        "uid" => $this->generate_uid(UID_ORDERS_ITEMS),
                        "order_id" => $orderData['uid'],
                        "product_id" => $cart['product_id'],
                        "product_config_id" => $cart['variation_id'],
                        "price" => $priceAfterDiscount,
                        "qty" => $cart['qty'],
                        "size" => $cart['size'],
                    ];

                    if (!$OrderItemsModel->insert($OrderItemsData)) {
                        $OrderItemsIsSaved = false;
                        break;
                    } else {
                        // Update stock
                        $prev_stock = $ItemStocksModel->where('uid', $cart['item_stock_id'])->first();
                        $new_qty = max($prev_stock['stocks'] - $cart['qty'], 0);
                        $ItemStocksModel->where('uid', $cart['item_stock_id'])->set('stocks', $new_qty)->update();

                        // Add item details to Shiprocket order items array
                        $shiprocketOrderItems[] = [
                            "name" => $cart['product']['name'],
                            "sku" => $this->generateRandomSKU(),  // Ensure SKU is fetched correctly
                            "units" => (int) $cart['qty'],
                            "selling_price" => (float) $priceAfterDiscount,
                            "hsn" => ""  // Replace with a valid HSN code if required, or remove it if not
                        ];
                    }
                }
                // die();
                // Set The Response and call Shiprocket API if order saved successfully
                if ($OrdersIsSaved && $PaymentsIsSaved && $OrderItemsIsSaved) {
                    // Authenticate with Shiprocket
                    $shiprocketToken = $this->authenticateShiprocket(SHIPROCKET_EMAIL, SHIPROCKET_PASS);

                    if ($shiprocketToken) {
                        // $this->logoutShiprocket($shiprocketToken);
                        // Prepare the Shiprocket order data
                        $shiprocketOrderData = [
                            "order_id" => (string) $orderData['uid'],
                            "order_date" => date("Y-m-d H:i"),
                            "pickup_location" => "Primary",
                            "billing_customer_name" => $data['user_data']['name'],
                            "billing_last_name" => "",
                            "billing_address" => $data['user_data']['address']['locality'],
                            "billing_address_2" => $data['user_data']['address']['locality'],
                            "billing_city" => $data['user_data']['address']['city'],
                            "billing_pincode" => (string) $data['user_data']['address']['zipcode'],
                            "billing_state" => $data['user_data']['address']['state'],
                            "billing_country" => "India",
                            "billing_email" => $data['user_data']['email'],
                            "billing_phone" => (string) $data['user_data']['number'],
                            "shipping_is_billing" => true,
                            "order_items" => $shiprocketOrderItems,
                            "payment_method" => $data['payment_data']['method'],
                            "sub_total" => (float) $data['user_cart']['subTotal'],
                            "length" => 10,
                            "breadth" => 15,
                            "height" => 5,
                            "weight" => 0.5
                        ];

                        // $this->prd(json_encode($shiprocketOrderData));
                        // Call the Shiprocket API to create the order
                        $shiprocketResponse = $this->createCustomOrder($shiprocketToken, $shiprocketOrderData);
                        // $awb = $this->assignAwb($shiprocketToken, $orderData['uid']);
                        $this->logoutShiprocket($shiprocketToken);

                        if ($shiprocketResponse) {
                            $UserCartModel = new UserCartModel();
                            $UserCartModel->where('user_id', $orderData['user_id'])->delete();
                            $resp['status'] = true;
                            $resp['message'] = 'order placed';
                            $resp['data'] = ['order_id' => $orderData['uid']];

                        } else {
                            // $this->prd($shiprocketResponse);
                            $resp['message'] = 'Order placed locally, but failed on Shiprocket: ';
                        }
                    } else {
                        $resp['message'] = 'Order placed locally, but Shiprocket authentication failed.';
                    }
                } else {
                    $resp['message'] = 'Failed to place the order.';
                }
            } else {
                $resp['message'] = 'Please Add A Billing Address';
            }
        } catch (\Exception $e) {
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }

    private function add_vendor_commission($product)
    {
        if (!empty($product)) {
            $VendorModel = new VendorModel();
            $VendorWalletModel = new VendorWalletModel();
            $VendorWalletHistoryModel = new VendorWalletHistoryModel();
            $CommonModel = new CommonModel();

            try {
                // Fetch vendor user ID
                $vendor = $VendorModel->where('uid', $product['vendor_id'])->first();
                if (!$vendor) {
                    throw new \Exception("Vendor not found");
                }
                $venUser_id = $vendor['user_id'];

                // Fetch current wallet balance
                $wallet = $VendorWalletModel->where('user_id', $venUser_id)->first();
                if (!$wallet) {
                    throw new \Exception("Vendor wallet not found");
                }
                $currentWalletBalance = $wallet['balance'];

                // Calculate vendor's commission
                $price = $product['product_prices'][0]['price'];
                if (!defined('VENDOR_COMMISSION_PERCENTAGE')) {
                    throw new \Exception("Vendor commission percentage not defined");
                }
                $vendorCut = (VENDOR_COMMISSION_PERCENTAGE / 100) * $price;

                // Update wallet balance
                $newBalance = $currentWalletBalance + $vendorCut;
               
                $sql = "UPDATE 
                            vendor_wallet
                        SET 
                            balance = '".floatval($newBalance)."',
                            updated_at = '".date('Y-m-d H:i:s')."' 
                        WHERE user_id = '".$venUser_id."'";
                $CommonModel->customQuery($sql);

                $historyData = [
                    'uid' => $this->generate_uid('VWH'),
                    'user_id' => $venUser_id,
                    "credited" => $vendorCut,
                    "debited" => '0',
                    "closing_balance" => $newBalance
                ];
                $VendorWalletHistoryModel->insert($historyData);
                // Return user ID
                return $newBalance;

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        return "Invalid product data";
    }



    private function order_details($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Cannot Find Details',
            'data' => []
        ];
        $order_id = $data['o_id'];

        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT
                        orders.uid AS order_id,
                        orders.user_id,
                        orders.shipping_method,
                        orders.user_name,
                        orders.phone_number,
                        orders.email,
                        orders.order_discount_amount,
                        orders.order_discount_percentage,
                        orders.sub_total,
                        orders.total,
                        orders.order_status,
                        orders.status,
                        orders.payment_type,
                        orders.created_at
                    FROM
                        orders
                    WHERE
                        orders.uid = '{$order_id}'";
            $order = $CommonModel->customQuery($sql);
            $order = json_decode(json_encode($order), true);
            $order = !empty($order) ? $order[0] : null;

            $UsersModel = new UsersModel();
            $order['user'] = $UsersModel->where('uid', $order['user_id'])->findAll();
            $order['user'] = !empty($order['user']) ? $order['user'][0] : null;

            $PaymentsModel = new PaymentsModel();
            $order['payment'] = $PaymentsModel->where('order_id', $order_id)->findAll();
            $order['payment'] = !empty($order['payment']) ? $order['payment'][0] : null;

            $AddressModel = new AddressModel();
            $order['address'] = $AddressModel->where('user_id', $order['user_id'])->find();
            $order['address'] = !empty($order['address']) ? $order['address'][0] : null;

            $OrderItemsModel = new OrderItemsModel();
            $order['products'] = $OrderItemsModel->where('order_id', $order_id)->findAll();
            if (count($order['products']) > 0) {
                $Product_Controller = new Product_Controller();
                foreach ($order['products'] as $index => $item) {
                    if (!empty($item['product_config_id'])) {
                        $product_details = $Product_Controller->products(['p_id' => $item['product_id']]);
                        $product_details = json_decode(json_encode($product_details['data']), true);
                        $order['products'][$index]['product_details'] = $product_details;
                        $product_img = $Product_Controller->product_config_iamges($item['product_config_id']);
                        $order['products'][$index]['product_details']['product_img'] = $product_img['data'];

                    } else {
                        $product_details = $Product_Controller->products(['p_id' => $item['product_id']]);
                        $product_details = json_decode(json_encode($product_details['data']), true);
                        $order['products'][$index]['product_details'] = $product_details;
                    }
                }
            }

            $resp['status'] = !empty($order);
            $resp['message'] = !empty($order) ? 'Order details found' : 'Cannot Find Details';
            $resp['data'] = !empty($order) ? $order : [];

            //$this->prd($order);
        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }

    private function user_orders($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        try {
            $user_id = $data['user_id'];
            $OrdersModel = new OrdersModel();
            $orders = $OrdersModel
                ->select('uid as order_id')
                ->where('user_id', $user_id)
                ->findAll();
            if (count($orders) > 0) {
                foreach ($orders as $index => $item) {
                    $order = $this->order_details(['o_id' => $item['order_id']]);
                    $orders[$index] = $order['data'];
                }
            } else {
                $orders = [];
            }
            if (count($orders) > 0) {
                $resp = [
                    'status' => true,
                    'message' => 'Orders found',
                    'data' => $orders
                ];
            }
        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }


    private function all_orders($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        try {
            $OrdersModel = new OrdersModel();
            $orders = $OrdersModel
                ->select('uid as order_id')
                ->orderBy('created_at', 'ASC')
                ->findAll();
            //$this->prd($orders);
            if (count($orders) > 0) {
                foreach ($orders as $index => $item) {
                    $order = $this->order_details(['o_id' => $item['order_id']]);
                    $orders[$index] = $order['data'];
                }
            } else {
                $orders = [];
            }
            if (count($orders) > 0) {
                $resp = [
                    'status' => true,
                    'message' => 'Orders found',
                    'data' => $orders
                ];
            }

            //$this->prd($orders);

        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }



        return $resp;
    }

    private function order_cancel($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        //$this->prd($data);

        try {
            $OrdersModel = new OrdersModel();
            $OrdersCancelledModel = new OrdersCancelledModel();
            $cancel_data = [
                'uid' => $this->generate_uid(TABLE_ORDER_CANCEL),
                'order_id' => $data['o_id'],
                'reason' => $data['reason']
            ];
            $OrdersCancelledModel->insert($cancel_data);


            // Assuming $data['order_id'] contains the ID of the order to cancel
            $order_id = $data['o_id'];

            // Update the status of the order to 'cancelled'
            $isUpdated = $OrdersModel->set('order_status', 'cancelled')
                ->where('uid', $order_id)
                ->update();


            if ($isUpdated) {
                $shiprocketToken = $this->authenticateShiprocket(SHIPROCKET_EMAIL, SHIPROCKET_PASS);
                $shiprocketResponse = $this->cancelShiprocketOrder($shiprocketToken, $order_id);
                $this->prd($order_id);
                $resp['status'] = true;
                $resp['message'] = 'Order cancelled successfully';
                $resp['data'] = $shiprocketResponse;
            } else {
                $resp['message'] = 'Failed to cancel order';
            }
        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }


    private function order_status_update($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            $order_id = $data['o_id'];
            $order_status = $data['order_status'];

            $OrdersModel = new OrdersModel();
            $isUpdated = $OrdersModel->set('order_status', $order_status)
                ->where('uid', $order_id)
                ->update();

            if ($isUpdated) {
                $resp['status'] = true;
                $resp['message'] = 'Order Updated successfully';
                $resp['data'] = ['order_id' => $order_id];
            } else {
                $resp['message'] = 'Failed to Updated order';
            }

        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }

    private function order_return_request($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            date_default_timezone_set('Asia/Kolkata');
            $OrdersReturnModel = new OrdersReturnModel();
            $return_data = [
                'uid' => $this->generate_uid(UID_RETURN),
                'order_id' => $data['o_id'],
                'order_item_id' => !empty($data['p_id']) ? $data['p_id'] : '',
                'reason' => $data['reason'],
                'status' => 'requested',
                'type' => !empty($data['p_id']) ? 'item' : 'order',
                "created_at" => date('Y-m-d H:i:s'),
            ];
            $isInserted = $OrdersReturnModel->insert($return_data);

            // Check if insertion was successful
            if ($isInserted) {
                $OrdersModel = new OrdersModel();
                $OrdersModel->set('order_status', 'return_requested')
                    ->where('uid', $data['o_id'])
                    ->update();

                // Update response for success case
                $resp['status'] = true;
                $resp['message'] = 'Return request submitted successfully';
                $resp['data'] = ['return_uid' => $return_data['uid']];
            } else {
                // If insertion failed, update error message
                $resp['message'] = 'Failed to submit return request';
            }

        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }




    private function user_order_returns($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT 
                        orders_return.uid AS return_id,
                        orders_return.order_id AS order_id,
                        orders_return.status AS status,
                        orders_return.reason AS reason,
                        orders_return.order_item_id AS item_id,
                        orders_return.type AS type,
                        orders_return.created_at AS request_date,
                        orders.total AS total,
                        orders.user_id AS user_id,
                        users.user_name AS user_name
                    FROM 
                        orders_return
                    JOIN
                        orders ON orders_return.order_id = orders.uid
                    JOIN
                        users ON orders.user_id = users.uid";
            if (isset($data['user_id'])) {
                $user_id = $data['user_id'];
                $sql .= " WHERE orders.user_id = '{$user_id}';";
            } else if (isset($data['r_id'])) {
                $return_id = $data['r_id'];
                $sql .= " WHERE orders_return.uid = '{$return_id}';";
            }
            $return = $CommonModel->customQuery($sql);
            $return = json_decode(json_encode($return), true);
            if (!empty($return)) {
                $resp['status'] = true;
                $resp['message'] = 'Order returns found';
                $resp['data'] = isset($data['r_id']) ? $return[0] : $return;
            } else {
                $resp['message'] = 'No returns found';
            }

        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;

    }

    private function seller_order_return_request($data)
    {

        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT 
                        orders_return.uid AS return_id,
                        orders_return.order_id AS order_id,
                        orders_return.status AS status,
                        orders_return.reason AS reason,
                        orders_return.order_item_id AS item_id,
                        orders_return.type AS type,
                        orders_return.created_at AS request_date,
                        orders.total AS total,
                        order_items.price AS product_price,
                        order_items.qty AS product_qty,
                        product.vendor_id AS vendor_id,
                        orders.user_id AS user_id,
                        users.user_name AS user_name
                    FROM 
                        orders_return
                    JOIN
                        orders ON orders_return.order_id = orders.uid
                    JOIN
                        order_items ON orders.uid = order_items.order_id
                    JOIN 
                        product ON order_items.product_id = product.uid
                    JOIN
                        users ON orders.user_id = users.uid";
            if (isset($data['user_id'])) {
                $user_id = $data['user_id'];
                $sql .= " WHERE orders.user_id = '{$user_id}';";
            } else if (isset($data['r_id'])) {
                $return_id = $data['r_id'];
                $sql .= " WHERE orders_return.uid = '{$return_id}';";
            }
            $return = $CommonModel->customQuery($sql);
            $return = json_decode(json_encode($return), true);
            if (!empty($return)) {
                $resp['status'] = true;
                $resp['message'] = 'Order returns found';
                $resp['data'] = isset($data['r_id']) ? $return[0] : $return;
            } else {
                $resp['message'] = 'No returns found';
            }

        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;

    }

    private function order_return_status_update($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            $r_id = $data['r_id'];
            $o_id = $data['o_id'];
            $return_status = $data['return_status'];

            $OrdersReturnModel = new OrdersReturnModel();
            $isUpdated = $OrdersReturnModel->set('status', $return_status)
                ->where('uid', $r_id)
                ->update();

            $OrdersModel = new OrdersModel();
            if ($return_status == 'accepted') {
                $OrdersModel->set('order_status', 'return_accepted')
                    ->where('uid', $o_id)
                    ->update();
            } elseif ($return_status == 'rejected') {
                $OrdersModel->set('order_status', 'return_rejected')
                    ->where('uid', $o_id)
                    ->update();
            } elseif ($return_status == 'returned') {
                $OrdersModel->set('order_status', 'order_returned')
                    ->where('uid', $o_id)
                    ->update();
            }


            if ($isUpdated) {
                $resp['status'] = true;
                $resp['message'] = 'Order Updated successfully';
                $resp['data'] = ['return_id' => $r_id];
            } else {
                $resp['message'] = 'Failed to Updated order';
            }

        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;

    }

    private function order_payment_status_update($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Order not found',
            'data' => []
        ];

        try {
            $pay_id = $data['pay_id'];
            $status = $data['status'];
            $PaymentsModel = new PaymentsModel();
            $isUpdated = $PaymentsModel->set('status', $status)
                ->where('uid', $pay_id)
                ->update();

            if ($isUpdated) {
                $resp['status'] = true;
                $resp['message'] = 'Order Updated successfully';
                $resp['data'] = ['return_id' => $pay_id];
            } else {
                $resp['message'] = 'Failed to Updated order';
            }
        } catch (\Exception $e) {
            // Handle any errors
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }

    private function total_order()
    {
        $resp = [
            'status' => false,
            'message' => 'no order found',
            'data' => 0
        ];
        try {
            $OrdersModel = new OrdersModel();
            $totalOrder = $OrdersModel->countAll();
            if (!empty($totalOrder)) {
                $resp = [
                    'status' => true,
                    'message' => 'Total order found',
                    'data' => $totalOrder
                ];
            }
        } catch (\Exception $e) {
            $resp['message'] = $e;
        }
        return $resp;
    }

    private function revenue()
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        try {
            $OrdersModel = new OrdersModel();
            $delivered_orders = $OrdersModel
                ->select('*')
                ->where('order_status', 'delivered')
                ->findAll();
            $cancelled_orders = $OrdersModel
                ->select('*')
                ->where('order_status', 'cancelled')
                ->findAll();
            // if (count($delivered_orders) > 0) {
            $resp = [
                'status' => true,
                'message' => 'Orders found',
                'data' => ['delivered_orders' => $delivered_orders, 'cancelled_orders' => $cancelled_orders]
            ];
            // }
            // if (count($cancelled_orders) > 0) {
            //     $resp = [
            //         'status' => true,
            //         'message' => 'Orders found',
            //         'data' => ['cancelled_orders' => $cancelled_orders]
            //     ];
            // }

            //$this->prd($orders);

        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }



        return $resp;
    }

    // private function best_selling()
    // {
    //     $resp = [
    //         'status' => false,
    //         'message' => 'Orders not found',
    //         'data' => []
    //     ];

    //     try {

    //         $OrderItemsModel = new OrderItemsModel();
    //         $product = $OrderItemsModel
    //                 ->distinct()
    //                 ->select('product_id')
    //                 ->findAll();
    //         $all_product_qty = array();
    //         if (count($product) > 0) {
    //             $i=0;
    //             foreach ($product as $index => $item) {
    //                 $i++;
    //                 $quantity = $OrderItemsModel
    //                         ->select('product_id, qty, order_id, uid')
    //                         ->where('product_id', $item)
    //                         ->findAll();
    //                 $total_qty = 0;
    //                 foreach ($quantity as $index => $qty) {
    //                     $total_qty = $total_qty + $qty['qty'];
    //                 }
    //                 $all_product_qty[$i]['total_qty'] = $total_qty;
    //                 $all_product_qty[$i]['product_id'] = $item['product_id'];

    //             }
    //             $totalQty = array();
    //             foreach ($all_product_qty as $key => $row) {
    //                 $totalQty[$key] = $row['total_qty'];
    //             }
    //             array_multisort($totalQty, SORT_DESC, $all_product_qty);

    //             // $ProductModel = new ProductModel();
    //             $product = "";
    //             foreach($all_product_qty as $product_qty){
    //                 // $product = $ProductModel
    //                 //         ->select('*')
    //                 //         ->where('uid', $product_qty['product_id'])
    //                 //         ->first();
    //                 $product = products($product_qty['product_id']);
    //             }


    //             $this->pr($product);
    //             die();

    //         }


    //         if (count($orders) > 0) {
    //             $resp = [
    //                 'status' => true,
    //                 'message' => 'Orders found',
    //                 'data' => $orders
    //             ];
    //         }

    //         //$this->prd($orders);

    //     } catch (\Exception $e) {
    //         // Handle Any Error
    //         $resp['message'] = $e->getMessage();
    //     }



    //     return $resp;
    // }


    private function seller_order($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        try {
            $OrdersModel = new OrdersModel();
            $orders = $OrdersModel
                ->select('uid as order_id')
                ->orderBy('created_at', 'ASC')
                ->findAll();
            //$this->prd($orders);
            if (count($orders) > 0) {
                foreach ($orders as $index => $item) {
                    $order = $this->order_details(['o_id' => $item['order_id']]);
                    $orders[$index] = $order['data'];
                }
            } else {
                $orders = [];
            }
            if (count($orders) > 0) {
                $VendorModel = new VendorModel();
                $vendor_id = $VendorModel->select('uid')
                    ->where('user_id', $data['v_id'])
                    ->findAll();
                $vendor_id = !empty($vendor_id) ? $vendor_id[0]['uid'] : '';
                $filteredOrders = [];
                foreach ($orders as $oIndex => $oItem) {

                    foreach ($oItem['products'] as $product) {
                        if (!empty($product['product_details'])) {
                            if ($product['product_details']['vendor_id'] === $vendor_id) {
                                // If a product with the desired vendor ID is found, add the order to the filtered array
                                $filteredOrders[] = $oItem;
                                // Break out of the inner loop since we found a match for this order
                                break;
                            }
                        }
                    }

                }


                $resp = [
                    'status' => true,
                    'message' => 'Orders found',
                    'data' => $filteredOrders
                ];
            }
        } catch (\Exception $e) {
            $resp['message'] = $e->getMessage();
        }

        return $resp;

    }

    private function order_item_status_update($data)
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        try {
            $order_item_id = $data['order_item_id'];
            $status = $data['status'];
            $OrderItemsModel = new OrderItemsModel();
            $isUpdated = $OrderItemsModel->set('status', $status)
                ->where('uid', $order_item_id)
                ->update();
            if ($isUpdated) {
                $resp['status'] = true;
                $resp['message'] = 'Order Updated successfully';
                $resp['data'] = ['order_item_id' => $order_item_id];
            } else {
                $resp['message'] = 'Failed to Updated order';
            }


        } catch (\Exception $e) {
            $resp['message'] = $e->getMessage();
        }

        return $resp;
    }

    private function total_selling_item()
    {
        $resp = [
            'status' => false,
            'message' => 'no order found',
            'data' => 0
        ];

        $seller_id = $this->is_seller_logedin();
        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT
                COUNT(*)
            FROM
                order_items
            JOIN
                product ON product.uid = order_items.product_id";

            if (!empty($seller_id)) {
                $sql .= " WHERE
                    product.vendor_id = '{$seller_id}';";

            }

            $total_item = $CommonModel->customQuery($sql);


            if (!empty($total_item)) {
                $resp = [
                    'status' => true,
                    'message' => 'Total order found',
                    'data' => $total_item[0]
                ];
            }
        } catch (\Exception $e) {
            $resp['message'] = $e;
        }
        return $resp;
    }

    private function seller_revenue()
    {
        $resp = [
            'status' => false,
            'message' => 'Orders not found',
            'data' => []
        ];

        $seller_id = $this->is_seller_logedin();

        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT*
            FROM
                order_items
            JOIN
                product ON product.uid = order_items.product_id
            WHERE 
                order_items.status = 'delivered'";

            if (!empty($seller_id)) {
                $sql .= " AND product.vendor_id = '{$seller_id}'";
            }

            $delivered_orders = $CommonModel->customQuery($sql);
            // $this->prd($seller_id);

            $sql2 = "SELECT*
            FROM
                order_items
            JOIN
                product ON product.uid = order_items.product_id
                WHERE order_items.status = 'cancelled'";

            if (!empty($seller_id)) {
                $sql2 .= " AND product.vendor_id = '{$seller_id}'";
            }

            $cancelled_orders = $CommonModel->customQuery($sql2);

            // if (count($delivered_orders) > 0 || count($cancelled_orders) > 0) {
            $resp = [
                'status' => true,
                'message' => 'Orders found',
                'data' => ['delivered_orders' => $delivered_orders, 'cancelled_orders' => $cancelled_orders]
            ];
            // }
            // if (count($cancelled_orders) > 0) {
            //     $resp = [
            //         'status' => true,
            //         'message' => 'Orders found',
            //         'data' => ['cancelled_orders' => $cancelled_orders]
            //     ];
            // }

            //$this->prd($orders);

        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }



        return $resp;
    }

    private function seller_earning()
    {
        $total_earning = 0;
        $resp = [
            'status' => false,
            'message' => 'Total Erning not found',
            'data' => $total_earning
        ];

        $seller_id = $this->is_seller_logedin();

        try {
            $CommonModel = new CommonModel();
            $sql = "SELECT*
            FROM
                order_items
            JOIN
                product ON product.uid = order_items.product_id";

            if (!empty($seller_id)) {
                $sql .= " WHERE
                            product.vendor_id = '{$seller_id}'";
            }

            $product = json_decode(json_encode($CommonModel->customQuery($sql)), true);

            // $this->prd($product);

            foreach ($product as $item) {
                $total_earning = $total_earning + $item['price'] * $item['qty'];
            }
            // $this->pr($total_earning);
            // die();
            if ($total_earning) {
                $resp = [
                    'status' => true,
                    'message' => 'Total Erning found',
                    'data' => $total_earning
                ];
            }

        } catch (\Exception $e) {
            // Handle Any Error
            $resp['message'] = $e->getMessage();
        }



        return $resp;
    }
















    public function GET_order_payment_status_update()
    {
        $data = $this->request->getGET();
        $resp = $this->order_payment_status_update($data);
        return $this->response->setJSON($resp);
    }

    public function GET_seller_order_return_request()
    {
        $data = $this->request->getGET();
        $resp = $this->seller_order_return_request($data);
        return $this->response->setJSON($resp);
    }

    public function GET_order_item_status_update()
    {
        $data = $this->request->getGET();
        $resp = $this->order_item_status_update($data);
        return $this->response->setJSON($resp);
    }


    public function GET_seller_order()
    {
        $data = $this->request->getGET();
        $resp = $this->seller_order($data);
        return $this->response->setJSON($resp);
    }


    public function GET_user_order_returns()
    {
        $data = $this->request->getGET();
        $resp = $this->user_order_returns($data);
        return $this->response->setJSON($resp);
    }

    public function GET_order_return_status_update()
    {
        $data = $this->request->getGET();
        $resp = $this->order_return_status_update($data);
        return $this->response->setJSON($resp);
    }

    public function GET_order_return_request()
    {
        $data = $this->request->getGET();
        $resp = $this->order_return_request($data);
        return $this->response->setJSON($resp);
    }


    public function POST_order_confirm()
    {
        $data = $this->request->getJSON();
        $resp = $this->order_confirm($data);
        return $this->response->setJSON($resp);

    }

    public function GET_order_details()
    {
        $data = $this->request->getGET();
        $resp = $this->order_details($data);
        return $this->response->setJSON($resp);
    }


    public function GET_user_orders()
    {
        $data = $this->request->getGET();
        $resp = $this->user_orders($data);
        return $this->response->setJSON($resp);
    }


    public function GET_all_orders()
    {

        $data = $this->request->getGET();
        $resp = $this->all_orders($data);
        return $this->response->setJSON($resp);

    }

    public function GET_order_cancel()
    {
        $data = $this->request->getGET();
        $resp = $this->order_cancel($data);
        return $this->response->setJSON($resp);
    }

    public function GET_order_status_update()
    {

        $data = $this->request->getGET();
        $resp = $this->order_status_update($data);
        return $this->response->setJSON($resp);

    }

    public function GET_total_order()
    {
        $resp = $this->total_order();
        return $this->response->setJSON($resp);
    }

    // public function GET_best_selling()
    // {
    //     $resp = $this->best_selling();
    //     return $this->response->setJSON($resp);
    // }

    public function GET_revenue()
    {
        $resp = $this->revenue();
        return $this->response->setJSON($resp);
    }

    public function GET_total_selling_item()
    {
        $resp = $this->total_selling_item();
        return $this->response->setJSON($resp);
    }

    public function GET_seller_revenue()
    {
        $resp = $this->seller_revenue();
        return $this->response->setJSON($resp);
    }

    public function GET_seller_earning()
    {
        $resp = $this->seller_earning();
        return $this->response->setJSON($resp);
    }

}