See Documentation here - https://developer.sslcommerz.com/registration

### # CSRF Token Error / 419
In Laravel, you can handle CSRF Token Errors (status code 419) by customizing the "VerifyCsrfToken middleware", which is located in the "app/Http/Middleware/VerifyCsrfToken.php" file. To address this error, you need to declare the success, fail, and cancel URLs/routes in the middleware.


```
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'success',
        'fail',
        'cancel'
    ];
}
```

### Handle Session Log Out Error in Laravel
The error is related to the 'same_site' and 'secure' options in the config/session.php configuration file.

<pre>
Change 'secure' => env('SESSION_SECURE_COOKIE'), to 'secure' => true;<br>
Change 'same_site' => 'lax', to 'same_site' => 'none';
</pre>

### Update .env file
``` 
SSLCOMMERZ_STORE_ID= Your store id goes here
SSLCOMMERZ_STORE_PASSWORD= Your store password goes here
```

### Create a file in config/sslcommerz.php and update it by the following code
``` 
<?php

return [
    'store_id'       => env('SSLCOMMERZ_STORE_ID'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
];
```

### Update routes/web.php file

``` 
Route::get('payment',[PaymentController::class,'payment'])->name('payment');

Route::post('success',[PaymentController::class,'success'])->name('success');
Route::post('fail',[PaymentController::class,'fail'])->name('fail');
Route::post('cancel',[PaymentController::class,'cancel'])->name('cancel');

Route::post('ipn',[PaymentController::class,'ipn'])->name('ipn');

```

### Create PaymentController.php file and update it by the following code

``` 
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        try {
            $post_data = array();
            $post_data['store_id'] = config('sslcommerz.store_id');
            $post_data['store_passwd'] = config('sslcommerz.store_password');
            $post_data['total_amount'] = "103";
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = "Trx_".rand(00000000, 99999999);
            $post_data['success_url'] = route('success');
            $post_data['fail_url'] = route('fail');
            $post_data['cancel_url'] = route('cancel');
# $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

# EMI INFO
            $post_data['emi_option'] = "0";
            $post_data['emi_max_inst_option'] = "9";
            $post_data['emi_selected_inst'] = "9";

# CUSTOMER INFORMATION
            $post_data['cus_name'] = "Test Customer";
            $post_data['cus_email'] = "test@test.com";
            $post_data['cus_add1'] = "Dhaka";
            $post_data['cus_add2'] = "Dhaka";
            $post_data['cus_city'] = "Dhaka";
            $post_data['cus_state'] = "Dhaka";
            $post_data['cus_postcode'] = "1000";
            $post_data['cus_country'] = "Bangladesh";
            $post_data['cus_phone'] = "01711111111";
            $post_data['cus_fax'] = "01711111111";

# SHIPMENT INFORMATION
            $post_data['shipping_method'] = "No";
            $post_data['ship_name'] = "Store Test";
            $post_data['ship_add1 '] = "Dhaka";
            $post_data['ship_add2'] = "Dhaka";
            $post_data['ship_city'] = "Dhaka";
            $post_data['ship_state'] = "Dhaka";
            $post_data['ship_postcode'] = "1000";
            $post_data['ship_country'] = "Bangladesh";

# OPTIONAL PARAMETERS
            $post_data['value_a'] = "ref001";
            $post_data['value_b '] = "ref002";
            $post_data['value_c'] = "ref003";
            $post_data['value_d'] = "ref004";

# CART PARAMETERS
            $post_data['product_name'] = "Computer";
            $post_data['product_category'] = "Electronic ";
            $post_data['product_profile'] = "general";

            $post_data['cart'] = json_encode(array(
                array("product"=>"DHK TO BRS AC A1","amount"=>"200.00"),
                array("product"=>"DHK TO BRS AC A2","amount"=>"200.00"),
                array("product"=>"DHK TO BRS AC A3","amount"=>"200.00"),
                array("product"=>"DHK TO BRS AC A4","amount"=>"200.00")
            ));
            $post_data['product_amount'] = "100";
            $post_data['vat'] = "5";
            $post_data['discount_amount'] = "5";
            $post_data['convenience_fee'] = "3";

            //Call Payment Integrate Api
            $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v4/api.php";
            //For Live use "https://securepay.sslcommerz.com/gwprocess/v4/api.php";

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $direct_api_url );
            curl_setopt($handle, CURLOPT_TIMEOUT, 30);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($handle, CURLOPT_POST, 1 );
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC


            $content = curl_exec($handle );

            curl_close( $handle);


# PARSE THE JSON RESPONSE
            $sslcz = json_decode($content, true );

            if(isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL']!="" ) {
                # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
                # echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
                echo "<meta http-equiv='refresh' content='0;url=".$sslcz['GatewayPageURL']."'>";
                # header("Location: ". $sslcz['GatewayPageURL']);
                exit;
            } else {
                return redirect()->route('home')->with('error', 'FAILED TO CONNECT WITH SSLCOMMERZ API');
            }
        }catch (\Exception $exception){
            return redirect()->route('home')->with('error', 'Something went wrong!');

        }

    }

    public function success(Request $request)
    {
        if ($request['status'] == 'VALID' || $request['status'] == 'VALIDATED' && $request['val_id'] != null) {
            try {
                $val_id=urlencode($request['val_id']);
                $store_id=urlencode(config('sslcommerz.store_id'));
                $store_passwd=urlencode(config('sslcommerz.store_password'));
                $requested_url = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=1&format=json");
                //For live use ("https://securepay.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=1&format=json");

                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $requested_url);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

                $result = curl_exec($handle);

                curl_close( $handle);

                $response = json_decode($result, true);
                //Store Transaction Id
                return redirect()->route('home')->with('success', 'Order placed successfully');

            }catch (\Exception $e){
                dd($e->getMessage());
            }

        }else{
            return redirect()->route('home')->with('warning', 'Invalid Transaction');
        }
    }

    public function fail(Request $request)
    {
        return redirect()->route('home')->with('warning', 'Order failed!');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('home')->with('warning', 'Order cancelled!');
    }

    public function ipn(Request $request)
    {
        $order = new Order();

        $order->transaction_id = $request->tran_id;
        $order->amount = $request->amount;
        $order->payment_method = $request->card_type;
        $order->save();
    }

}


``` 
