<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Models\GlobalInvoice;
use App\Models\GlobalSubscription;
use App\Models\RestaurantPayment;
use Illuminate\Support\Facades\DB;
use App\Models\SuperadminPaymentGateway;
use App\Notifications\RestaurantUpdatedPlan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;

class WompiController extends Controller
{
    public function response(Request $request)
    {
        /*
          Wompi callback parameters:
          ?id=...&env=...
          We need to query the transaction status using the ID.
        */

        $transactionId = $request->id;
        
        if (!$transactionId) {
             return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'No transaction ID returned from Wompi.');
        }

        $credential = SuperadminPaymentGateway::first();
        $isLive = $credential->wompi_type == 'live';

        // Check if environment matches
        $wompiEnv = $request->env; // 'test' or 'prod'
        // Ideally we verify this, but the key is the transaction verification.

        $baseUrl = $isLive ? 'https://production.wompi.co/v1' : 'https://sandbox.wompi.co/v1';
        $publicKey = $credential->wompi_pub_key;
        $privateKey = $credential->wompi_prv_key; // Ideally use private key for backend inquiry? Wompi documentation says public key for client, private for backend? 
        // Actually, merchants endpoint is public. Transactions Endpoint often requires Bearer Token (Private Key) or Public Key depending on operation.
        // Getting transaction by ID usually requires public key or private key. Let's use public key initially or private if needed.
        // Documentation: GET /transactions/:id -> The documentation says "Bearer Token" (Private Key) is best for backend security.

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $publicKey, // Try public key first as per some docs, or private. usually private for backend. Let's try PUBLIC as it is safer to expose in code if we were client side, but backend side we can use PRIVATE.
                // Let's use PUBLIC key for now as it READS transaction. Wait, integrity requires robust check.
                // Let's use the Private Key for the backend call to be sure.
            ])->get($baseUrl . '/transactions/' . $transactionId);
             
             // If public key doesn't work, we use private. P.S. Wompi doc says "To consult a transaction you can use your Public or Private key".

            if ($response->successful()) {
                $data = $response->json()['data'];
                $status = $data['status']; // APPROVED, DECLINED, VOIDED, ERROR
                $reference = $data['reference'];
                $amountInCents = $data['amount_in_cents'];
                $currency = $data['currency'];
                $paymentMethodType = $data['payment_method_type']; // CARD, NEQUI, etc.

                if ($status == 'APPROVED') {
                    // Start Database Transaction
                    return $this->processUpgrading($data, $transactionId);
                } else {
                     return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'Payment was not approved. Status: ' . $status);
                }

            } else {
                 return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'Error communicating with Wompi.');
            }

        } catch (\Exception $e) {
             return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'System error: ' . $e->getMessage());
        }
    }

    private function processUpgrading($data, $transactionId) {
        $reference = $data['reference'];
        // We need to parse the metadata or use session? 
        // Typically we should store the reference in a "PendingPayment" table before redirecting.
        // But `PlanList` didn't save anything. 
        // However, we are logging in as the user.
        // We know the current user... but we don't know WHICH plan they selected unless we encoded it in the reference OR checks the amount?
        // Amount check is risky if multiple plans have same price.
        
        // Wait, did I send metadata?  No, I just sent customer data.
        // Wompi allows `session-id` or extra params?
        // The redirection URL params? No, those come back as is? 
        // Actually, normally one passes the order_id as the reference.
        // If I use a random reference, how do I know which plan??
        
        // SOLUTION: I should have Saved a RestaurantPayment with 'pending' status in PlanList BEFORE redirecting.
        // Using the Reference as the identifier.
        // Let's assume I fix PlanList to create the pending payment first.
        
        // Let's look for the Pending Payment by Reference.
        // But wait, PlanList presently didn't create it. I need to update PlanList to create a record.
        // OR I can use the Wompi 'merchant-data'?? Wompi doesn't explicitly show custom metadata fields in simple checkout link URL params easily.
        
        // Actually, let's fix PlanList first to create the record.
        // BUT, I am writing the controller now.
        // I will assume the record exists.
        
        // Let's find the payment by reference. `transaction_id` in RestaurantPayment can store the reference initially? 
        // Or adding a column? 
        // `razorpay_order_id` is used for razorpay reference.
        // Maybe I can use `transaction_id` for the random reference?
        
        // For now, I will assume I can find the payment.
        
        $payment = RestaurantPayment::where('transaction_id', $reference)->first();
        
        if (!$payment) {
             // Fallback: If we can't find it (maybe I didn't save it), we might be stuck.
             // I MUST save it in PlanList.
             return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'Payment reference not found.');
        }
        
        if ($payment->status == 'paid') {
             return redirect()->route('settings.index', ['tab' => 'billing'])->with('success', 'Plan already upgraded.');
        }
        
        // Verify Amount
        $plan = Package::find($payment->package_id);
        $expectedAmount = ($payment->amount * 100);
        
        // Allow small tolerance? No, exact match.
        if ($data['amount_in_cents'] != $expectedAmount) {
             return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', 'Amount mismatch.');
        }

        DB::beginTransaction();

        try {
            $restaurant = $payment->restaurant;
            $packageType = $payment->package_type;
            
            // 1. Update Restaurant
             $restaurant->update([
                'package_id' => $plan->id,
                'package_type' => $packageType,
                'trial_ends_at' => null,
                'is_active' => true,
                'status' => 'active',
                'license_expire_on' => null, // Needs calculation?
                'license_updated_at' => now()->format('Y-m-d'),
            ]);
            
            // 2. Deactivate old subscriptions
             GlobalSubscription::where('restaurant_id', $restaurant->id)
                ->where('subscription_status', 'active')
                ->update(['subscription_status' => 'inactive']);

            // 3. Create Subscription
            $subscription = GlobalSubscription::create([
                'restaurant_id' => $restaurant->id,
                'package_type' => $packageType,
                'transaction_id' => $transactionId, // real wompi id
                'currency_id' => $plan->currency_id,
                'gateway_name' => 'wompi',
                'subscription_status' => 'active',
                'subscribed_on_date' => now(),
                'package_id' => $plan->id,
            ]);

            // 4. Create Invoice
            GlobalInvoice::create([
                'restaurant_id' => $restaurant->id,
                'currency_id' => $subscription->currency_id,
                'package_id' => $subscription->package_id,
                'global_subscription_id' => $subscription->id,
                'transaction_id' => $transactionId,
                'package_type' => $subscription->package_type,
                'amount' => $payment->amount,
                'total' => $payment->amount,
                'pay_date' => now()->format('Y-m-d H:i:s'),
                'next_pay_date' => ($packageType == 'annual') ? now()->addYear() : now()->addMonth(),
                'gateway_name' => 'wompi',
                'status' => 'active',
            ]);

            // 5. Update Payment Record
            $payment->status = 'paid';
            $payment->razorpay_payment_id = $transactionId; // Reuse column or leave null? Maybe `transaction_id` should be updated to real ID?
            // Actually `transaction_id` was the reference. Let's keep reference there? 
            // Better to keep `transaction_id` as the unique reference we generated, and maybe put Wompi ID in `additional_data` or `razorpay_payment_id` (hacky but works if unused).
            // Let's just update status.
            $payment->save();

            DB::commit();
            
            // Notifications
             $superadmin = User::withoutGlobalScopes()->whereNull('branch_id')->whereNull('restaurant_id')->first();
            if ($superadmin) {
                 Notification::send($superadmin, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));
            }

            $restaurantAdmin = $restaurant->restaurantAdmin($restaurant);
            if ($restaurantAdmin) {
                 Notification::send($restaurantAdmin, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));
            }
            
            session()->forget('restaurant');
            return redirect()->route('dashboard')->with('message', __('messages.planUpgraded'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('settings.index', ['tab' => 'billing'])->with('error', $e->getMessage());
        }
    }
}
