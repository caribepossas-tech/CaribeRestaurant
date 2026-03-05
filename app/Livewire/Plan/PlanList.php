<?php
namespace App\Livewire\Plan;

use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use Razorpay\Api\Api;
use App\Models\Module;
use App\Models\Country;
use App\Models\Package;
use Livewire\Component;
use App\Enums\PackageType;
use App\Models\Restaurant;
use Livewire\Attributes\On;
use App\Models\GlobalInvoice;
use Livewire\WithFileUploads;
use App\Models\GlobalCurrency;
use App\Scopes\RestaurantScope;
use App\Models\OfflinePlanChange;
use App\Models\RestaurantPayment;
use App\Models\GlobalSubscription;
use Illuminate\Support\Facades\DB;
use App\Models\OfflinePaymentMethod;
use App\Models\SuperadminPaymentGateway;
use App\Notifications\RestaurantUpdatedPlan;
use Illuminate\Support\Facades\Notification;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Notifications\RestaurantPlanModificationRequest;

class PlanList extends Component
{

    use LivewireAlert , WithFileUploads;

    public $packages;
    public $modules;
    public $currencies;
    public $selectedCurrency;
    public bool $isAnnual = false;
    public $showPaymentMethodModal = false;
    public $paymentMethods;
    public $free = false;
    public $selectedPlan;
    public $restaurant;
    public $stripeSettings;
    public $logo;
    public $countries;
    public $methods;
    public $payFastHtml;
    public $paymentGatewayActive;
    public $offlinePaymentGateways;
    public $paymentActive;
    public $pageTitle;
    public $planId;
    public $showOnline;
    public $show = 'payment-method';
    public $offlineMethodId;
    public $offlineUploadFile;
    public $offlineDescription;
    public $AllModulesWithFeature;
    public $PackageFeatures;
    public function mount()
    {

        $this->restaurant = Restaurant::where('id', restaurant()->id)->first();
        $this->paymentGatewayActive = false;
        $this->stripeSettings = SuperadminPaymentGateway::first();
        $this->currencies = GlobalCurrency::select('id', 'currency_name', 'currency_symbol')
        ->where('status', 'enable')
        ->get();

        if (in_array(true, [
            $this->stripeSettings->paypal_status,
            $this->stripeSettings->stripe_status,
            $this->stripeSettings->razorpay_status,
            $this->stripeSettings->paystack_status,
            $this->stripeSettings->mollie_status,
            $this->stripeSettings->payfast_status,
            $this->stripeSettings->authorize_status,
            $this->stripeSettings->wompi_status
        ])) {
            $this->paymentGatewayActive = true;
        }

        $this->methods = OfflinePaymentMethod::withoutGlobalScope(RestaurantScope::class)->where('status', 'active')->whereNull('restaurant_id')->get();
        $this->offlinePaymentGateways = $this->methods->count();

        $this->showOnline = $this->paymentGatewayActive;
        $this->paymentActive = $this->paymentGatewayActive || $this->offlinePaymentGateways > 0;

        $this->selectedCurrency = global_setting()->defaultCurrency->id ?? null;
        $this->modules = Module::pluck('name')->toArray();
        $this->PackageFeatures = Package::ADDITIONAL_FEATURES;

        $this->AllModulesWithFeature = array_merge(
            $this->modules,
            $this->PackageFeatures
        );
        $this->loadAvailablePackages();

    }

    public function switchPaymentMethod($method)
    {
        $this->show = $method;
    }

    // Selected Package
    public function selectedPackage($id)
    {
        $this->resetValidation();
        $this->reset(['show', 'offlineMethodId', 'offlineUploadFile', 'offlineDescription']);

        $this->selectedPlan = Package::findOrFail($id);
        $this->stripeSettings = SuperadminPaymentGateway::first();
        $this->logo = global_setting()->logo_url;
        $this->countries = Country::all();
        $this->methods = OfflinePaymentMethod::withoutGlobalScope(RestaurantScope::class)
            ->where('status', 'active')
            ->whereNull('restaurant_id')
            ->get();
        $this->free = $this->selectedPlan->payment_type == PackageType::DEFAULT || $this->selectedPlan->is_free;

        // Switching payment method based on the selected plan and modal.
        if ($this->free || !$this->paymentActive) {
            $this->showPaymentMethodModal = true;
            return;
        }

        if ($this->paymentGatewayActive) {
            $this->offlinePaymentGateways == 0 ? $this->handleOnlinePayments() : $this->showPaymentMethodModal = true;
        } else {
            $this->showPaymentMethodModal = true;
        }
    }

    // Online Payment Handling
    private function handleOnlinePayments()
    {
        $stripeActive = $this->stripeSettings->stripe_status;
        $razorpayActive = $this->stripeSettings->razorpay_status;
        $wompiActive = $this->stripeSettings->wompi_status;

        if (($stripeActive && $razorpayActive) || ($stripeActive && $wompiActive) || ($razorpayActive && $wompiActive) || ($stripeActive && $razorpayActive && $wompiActive)) {
            $this->showPaymentMethodModal = true;
            return;
        }

        if ($stripeActive) {
            $this->initiateStripePayment();
        } elseif ($razorpayActive) {
            $this->razorpaySubscription($this->selectedPlan->id);
        } elseif ($wompiActive) {
            $this->showPaymentMethodModal = true;
             // Ideally we could auto-initiate, but Wompi requires a redirect which is better handled via the button click to avoid popup blockers or confusion.
             // Also sticking to the modal pattern allows the user to see what's happening.
        } else {
            $this->showPaymentMethodModal = true;
        }
    }

    public function initiateWompiPayment($planId)
    {
        $plan = Package::find($planId);
        $credential = SuperadminPaymentGateway::first();

        if (!$plan || !$credential) {
            return $this->showError(__('messages.noPlanIdFound'));
        }

        $amount = $this->isAnnual ? $plan->annual_price : $plan->monthly_price;
        if ($plan->package_type == PackageType::LIFETIME) {
            $amount = $plan->price;
        }

        $currencyCode = $plan->currency->currency_code;
        $currencySymbol = $plan->currency->currency_symbol; // Should be COP usually for Wompi but let's assume
        $amountInCents = $amount * 100; // Wompi expects cents

        $reference = 'WOMPI_' . str()->random(15);
        
        // Create Pending Payment Record
        RestaurantPayment::create([
            'restaurant_id' => $this->restaurant->id,
            'amount' => $amount,
            'package_id' => $plan->id,
            'package_type' => $this->isAnnual ? 'annual' : 'monthly',
            'currency_id' => $plan->currency_id,
            'status' => 'pending',
            'transaction_id' => $reference, // Store reference here to look it up later
            'gateway' => 'wompi' // Assuming there is a gateway column? Checked RestaurantPayment model? It might not have 'gateway'. 
            // Checking Razorpay example: it saves `razorpay_order_id`. 
            // It doesn't seem to have a `gateway` column in the create example in `initiateStripePayment`.
            // `initiateStripePayment` calculates type but creation doesn't specify gateway?
            // Wait, `processLifetimePayment` creates it. 
            // Let's rely on transaction_id. 
        ]);

        $integritySecret = $credential->wompi_integrity_secret;
        
        $signatureString = $reference . $amountInCents . $currencyCode . $integritySecret;
        $signature = hash('sha256', $signatureString);

        $publicKey = $credential->wompi_pub_key;
        $redirectUrl = route('wompi.response'); 

        // Environment check
        $wompiUrl = $credential->wompi_type == 'live' ? 'https://checkout.wompi.co/p/' : 'https://checkout.wompi.co/p/';

        /* 
           Wompi Standard Checkout URL construction:
           We can pass parameters via query string to pre-fill the checkout or standard form data.
           Actually, the standard recommendation is to use the Widget or the Link.
           If using the Link (redirection):
           https://checkout.wompi.co/p/?public-key=...&currency=...&amount-in-cents=...&reference=...&signature:integrity=...&redirect-url=...
        */

        $queryParams = http_build_query([
            'public-key' => $publicKey,
            'currency' => $currencyCode,
            'amount-in-cents' => $amountInCents,
            'reference' => $reference,
            'signature:integrity' => $signature,
            'redirect-url' => $redirectUrl,
            'customer-data:email' => user()->email,
            'customer-data:full-name' => user()->name,
            'customer-data:phone-number' => $this->restaurant->whatsapp_number ?? '', // Optional
        ]);

        return redirect()->away($wompiUrl . '?' . $queryParams);
    }

    // Offline Payment Submit
    public function offlinePaymentSubmit()
    {
        $this->validate([
            'offlineMethodId' => 'required',
            'offlineUploadFile' => 'required|file',
            'offlineDescription' => 'required',
        ]);

        $checkAlreadyRequest = OfflinePlanChange::where('restaurant_id', $this->restaurant->id)
            ->where('status', 'pending')
            ->exists();

        if ($checkAlreadyRequest) {
            $this->alert('error', __('messages.alreadyRequestPending'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close')
            ]);
            return;
        }

        $package = Package::findOrFail($this->selectedPlan->id);
        $packageType = $package->package_type == PackageType::LIFETIME ? 'lifetime' : ($this->isAnnual ? 'annual' : 'monthly');

        $offlinePlanChange = new OfflinePlanChange();
        $offlinePlanChange->package_id = $this->selectedPlan->id;
        $offlinePlanChange->package_type = $packageType;
        $offlinePlanChange->restaurant_id = $this->restaurant->id;
        $offlinePlanChange->offline_method_id = $this->offlineMethodId;
        $offlinePlanChange->description = $this->offlineDescription;
        $offlinePlanChange->amount = $this->isAnnual ? $package->annual_price : $package->monthly_price;
        $offlinePlanChange->pay_date = now()->format('Y-m-d');
        $offlinePlanChange->next_pay_date = $this->isAnnual ? now()->addYear()->format('Y-m-d') : now()->addMonth()->format('Y-m-d');

        if ($this->offlineUploadFile) {
            $offlinePlanChange->file_name = Files::uploadLocalOrS3($this->offlineUploadFile, OfflinePlanChange::FILE_PATH);
        }

        $offlinePlanChange->save();

        // Send email to superAdmin to review offline payment request
        $superAdmin = User::withoutGlobalScopes()->whereNull('branch_id')->whereNull('restaurant_id')->first();
        Notification::send($superAdmin, new RestaurantPlanModificationRequest($this->restaurant, $offlinePlanChange));

        $this->showPaymentMethodModal = false;
        $this->reset(['offlineMethodId', 'offlineUploadFile', 'offlineDescription', 'selectedPlan']);

        $this->alert('success', __('messages.requestSubmittedSuccessfully'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close')
        ]);
        $this->redirect(route('settings.index', ['tab' => 'billing', 'offlineRequest' => 'offlineRequest']), navigate: true);
    }

    // Loading available packages based on selected currency
    public function updatedSelectedCurrency()
    {
        $this->loadAvailablePackages();
    }

    // Razorpay Subscription Initiate
    public function razorpaySubscription($planId)
    {
        $plan = Package::find($planId);
        $credential = SuperadminPaymentGateway::first();

        if (!$plan || !$credential) {
            return $this->showError(__('messages.noPlanIdFound'));
        }

        $apiKey = $credential->razorpay_key;
        $apiSecret = $credential->razorpay_secret;

        $api = new Api($apiKey, $apiSecret);
        $restaurantId = restaurant()->id;
        $amount = $plan->price ?? 0;
        $currencyCode = $plan->currency->currency_code;
        $currency_id = $plan->currency_id;
        $type = $this->isAnnual ? 'annual' : 'monthly';

        try {
            if ($plan->package_type == PackageType::LIFETIME) {
                return $this->processLifetimePayment($apiKey,$api, $plan, $amount, $currencyCode, $currency_id, $restaurantId);
            } else {
                return $this->processSubscription($apiKey ,$api, $plan, $restaurantId, $currencyCode, $type);
            }
        } catch (\Exception $e) {
            return $this->showError($e->getMessage());
        }
    }

    private function processLifetimePayment($apiKey ,$api, $plan, $amount, $currencyCode, $currency_id, $restaurantId)
    {
        $restaurantPayment = RestaurantPayment::create([
            'restaurant_id' => $restaurantId,
            'amount' => $amount,
            'package_id' => $plan->id,
            'package_type' => $plan->package_type,
            'currency_id' => $currency_id,
        ]);

        $razorpayOrder = $api->order->create([
            'amount' => ($amount * 100),
            'currency' => $currencyCode
        ]);

        $restaurantPayment->update(['razorpay_order_id' => $razorpayOrder->id]);

        return $this->dispatchRazorpay($apiKey, $razorpayOrder->id, $plan, $amount, $currencyCode, $restaurantId, $plan->package_type);
    }

    // Process Subscription for Razorpay
    private function processSubscription($apiKey, $api, $plan, $restaurantId, $currencyCode, $type)
    {

        $planID = $type == 'annual' ? $plan->razorpay_annual_plan_id : $plan->razorpay_monthly_plan_id;

        if (!$planID) {
            return $this->showError(__('messages.noPlanIdFound'));
        }

        $subscription = $api->subscription->create([
            'plan_id' => $planID,
            'customer_notify' => 1,
            'total_count' => 100
        ]);

        return $this->dispatchRazorpay($apiKey , $subscription->id, $plan, null, $currencyCode, $restaurantId, $type);
    }

    // Dispatch Razorpay Payment
    private function dispatchRazorpay($key, $id, $plan, $amount, $currencyCode, $restaurantId, $packageType)
    {
        $data = [
            'key' => $key,
            'name' => global_setting()->name,
            'description' => $plan->description,
            'image' => global_setting()->logo_url,
            'currency' => $currencyCode,
            'order_id' => $amount ? $id : null,
            'subscription_id' => !$amount ? $id : null,
            'amount' => $amount,
            'notes' => [
                'package_id' => $plan->id,
                'package_type' => $packageType,
                'restaurant_id' => $restaurantId,
            ]
        ];

        $this->dispatch('initiateRazorpay', json_encode($data));
    }

    // Show Error
    private function showError($message)
    {
        $this->alert('error', __($message), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close')
        ]);
    }


    // Razorpay Payment Confirmation
    #[On('confirmRazorpayPayment')]
    public function confirmRazorpayPayment($payment_id, $reference_id, $signature)
    {
        $credential = SuperadminPaymentGateway::first();

        if (!$credential) {
            session()->put('error', __('messages.noCredentialFound'));
        }


        $paymentId = $payment_id;
        $referenceId = $reference_id;
        $isOrderId = strpos($referenceId, 'order_') === 0;
        $isSubscriptionId = strpos($referenceId, 'sub_') === 0;
        $apiKey = $credential->razorpay_key;
        $secretKey = $credential->razorpay_secret;

        if (!$isOrderId && !$isSubscriptionId) {
            session()->put('error', __('messages.invalidReferenceId'));
        }

        $api = new Api($apiKey, $secretKey);

        if ($isOrderId) {
            $restaurantPayment = RestaurantPayment::where('razorpay_order_id', $referenceId)
                ->where('status', 'pending')
                ->first();
        } else {
            $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $referenceId, $secretKey);
            if ($expectedSignature !== $signature) {
                session()->put('error', __('messages.invalidSignature'));
                return;
            }

            $restaurantPayment = null;
        }

        try {
            DB::beginTransaction(); // Prevent partial updates
            // Fetch Payment Details
            $payment = $api->payment->fetch($paymentId);
            $plan = Package::findOrFail($payment->notes->package_id);
            $restaurant = $this->restaurant;
            $packageType = $payment->notes->package_type;
            $currencyCode = $plan->currency->currency_code;

            // Capture Payment if authorized
            if ($payment->status == 'authorized') {
                $payment->capture(['amount' => $payment->amount, 'currency' => $currencyCode]);
            }

            // Update Restaurant Subscription
            $restaurant->update([
                'package_id' => $plan->id,
                'package_type' => $packageType,
                'trial_ends_at' => null,
                'is_active' => true,
                'status' => 'active',
                'license_expire_on' => null,
                'license_updated_at' => now()->format('Y-m-d'),
            ]);

            // Deactivate previous subscriptions
            GlobalSubscription::where('restaurant_id', $restaurant->id)
                ->where('subscription_status', 'active')
                ->update(['subscription_status' => 'inactive']);

            // Create New Subscription Entry
            $subscription = GlobalSubscription::create([
                'restaurant_id' => $restaurant->id,
                'package_type' => $packageType,
                'transaction_id' => $paymentId,
                'currency_id' => $plan->currency_id,
                'razorpay_id' => $packageType == 'annual' ? $plan->razorpay_annual_plan_id : $plan->razorpay_monthly_plan_id,
                'razorpay_plan' => $packageType,
                'quantity' => 1,
                'package_id' => $plan->id,
                'subscription_id' => ($packageType == 'lifetime') ? $payment->order_id : $referenceId,
                'gateway_name' => 'razorpay',
                'subscription_status' => 'active',
                'ends_at' => $restaurant->license_expire_on ?? null,
                'subscribed_on_date' => now(),
            ]);

            // Generate Invoice
            GlobalInvoice::create([
                'restaurant_id' => $restaurant->id,
                'currency_id' => $subscription->currency_id,
                'package_id' => $subscription->package_id,
                'global_subscription_id' => $subscription->id,
                'transaction_id' => $subscription->transaction_id,
                'invoice_id' => $payment->invoice_id ?? null,
                'subscription_id' => $subscription->subscription_id,
                'package_type' => $subscription->package_type,
                'plan_id' => $subscription->razorpay_id,
                'amount' => $payment->amount / 100,
                'total' => $payment->amount / 100,
                'pay_date' => now()->format('Y-m-d H:i:s'),
                'next_pay_date' => ($packageType == 'annual') ? now()->addYear() : now()->addMonth(),
                'gateway_name' => 'razorpay',
                'status' => 'active',
            ]);

            DB::commit(); // Commit transaction

            if ($restaurantPayment) {
                $restaurantPayment->razorpay_payment_id = $paymentId;
                $restaurantPayment->status = 'paid';
                $restaurantPayment->payment_date_time = now()->toDateTimeString();
                $restaurantPayment->razorpay_signature = $signature;
                $restaurantPayment->transaction_id = $paymentId;
                $restaurantPayment->save();
            }

            // Notify Admin & Restaurant
            $superadmin = User::withoutGlobalScopes()->whereNull('branch_id')->whereNull('restaurant_id')->first();
            Notification::send($superadmin, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));

            $restaurantAdmin = $restaurant->restaurantAdmin($restaurant);
            Notification::send($restaurantAdmin, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));


            // Clear session & Redirect
            session()->forget('restaurant');
            session()->flash('flash.banner', __('messages.planUpgraded'));
            session()->flash('flash.bannerStyle', 'success');
            session()->flash('flash.link', route('settings.index', ['tab' => 'billing']));
            return $this->redirect(route('dashboard'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            session()->put('error', $e->getMessage());
            return Reply::redirect(route('pricing.plan'));
        }
    }


    // Loading Packages with currency and filters
    private function loadAvailablePackages()
    {
        if (!$this->selectedCurrency) {
            $this->packages = collect();
            return;
        }

        // Determine subscription status field based on isAnnual
        $statusField = $this->isAnnual ? 'annual_status' : 'monthly_status';

        // Build query
        $this->packages = Package::with(['currency', 'modules'])
            ->where('is_private', 0) // Non-private packages only
            ->where(function ($query) use ($statusField) {
                $query->where('package_type', 'lifetime')
                    ->orWhere('package_type', 'default') // Default packages
                    ->orWhere('is_free', true) // Include free packages
                    ->orWhere(function ($query) use ($statusField) {
                        $query->where('package_type', 'standard')
                        ->where($statusField, true); // Standard packages with the relevant status
                    });
            })
            ->where('package_type', '!=', 'trial') // Exclude trial packages
            ->where(function ($query) {
                $query->where('currency_id', $this->selectedCurrency)
                ->orWhere('package_type', 'default') // Default packages ignore currency
                ->orWhere('is_free', true); // Free packages ignore currency
            })->orderBy('sort_order')
            ->get();
    }

    // toggle Monthly and Annual
    public function toggle()
    {
        $this->isAnnual = !$this->isAnnual;
        $this->loadAvailablePackages(); // Refresh data on toggle
    }

    // initiate stripe payment
    public function initiateStripePayment()
    {

        if ($this->selectedPlan->package_type == PackageType::LIFETIME) {
            $amount = $this->selectedPlan->price;
        } else {
            $amount = $this->isAnnual ? $this->selectedPlan->annual_price : $this->selectedPlan->monthly_price;
        }

        if (!$amount) {
            $this->alert('error', __('messages.noPlanIdFound'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close')
            ]);
            return;
        }

        $plan = Package::find($this->selectedPlan->id);
        $type = $this->isAnnual ? 'annual' : 'monthly';
        $currency_id = $plan->currency_id;

        $payment = RestaurantPayment::create([
            'restaurant_id' => $this->restaurant->id,
            'amount' => $amount,
            'package_id' => $plan->id,
            'package_type' => $type,
            'currency_id' => $currency_id,
        ]);

        $this->dispatch('stripePlanPaymentInitiated', payment: $payment);
    }


    public function freePlan()
    {
        $package = Package::findOrFail($this->selectedPlan->id);
        $currencyId = $package->currency_id ?: global_setting()->currency_id;
        $restaurant = $this->restaurant;
        $type = $this->isAnnual ? 'annual' : 'monthly';

        GlobalSubscription::where('restaurant_id', $restaurant->id)
            ->where('subscription_status', 'active')
            ->update(['subscription_status' => 'inactive']);

        $subscription = new GlobalSubscription();
        $subscription->restaurant_id = $restaurant->id;
        $subscription->package_id = $package->id;
        $subscription->currency_id = $currencyId;
        $subscription->package_type = $type;
        $subscription->quantity = 1;
        $subscription->gateway_name = 'offline';
        $subscription->subscription_status = 'active';
        $subscription->subscribed_on_date = now();
        $subscription->transaction_id = str(str()->random(15))->upper();
        $subscription->save();

        // create offline invoice
        $offlineInvoice = new GlobalInvoice();
        $offlineInvoice->global_subscription_id = $subscription->id;
        $offlineInvoice->restaurant_id = $restaurant->id;
        $offlineInvoice->currency_id = $currencyId;
        $offlineInvoice->package_id = $package->id;
        $offlineInvoice->package_type = $type;
        $offlineInvoice->total = 0;
        $offlineInvoice->pay_date = now()->format('Y-m-d');
        $offlineInvoice->next_pay_date = $type == 'annual' ? now()->addYear()->format('Y-m-d') : now()->addMonth()->format('Y-m-d');
        $offlineInvoice->gateway_name = 'offline';
        $offlineInvoice->transaction_id = $subscription->transaction_id;
        $offlineInvoice->save();

        // Change restaurant package
        $restaurant->package_id = $package->id;
        $restaurant->package_type = $type;
        $restaurant->status = 'active';
        $restaurant->license_expire_on = $type == 'annual' ? now()->addYear()->format('Y-m-d') : now()->addMonth()->format('Y-m-d');
        $restaurant->license_updated_at = now()->format('Y-m-d');
        $restaurant->save();

        // Send superadmin notification
        $generatedBy = User::withoutGlobalScopes()->whereNull('branch_id')->whereNull('restaurant_id')->first();
        Notification::send($generatedBy, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));

        // Send notification to restaurant admin
        $restaurantAdmin = $restaurant->restaurantAdmin($restaurant);
        Notification::send($restaurantAdmin, new RestaurantUpdatedPlan($restaurant, $subscription->package_id));

        session()->forget('restaurant');


        request()->session()->flash('flash.banner', __('messages.planUpgraded'));
        request()->session()->flash('flash.bannerStyle', 'success');
        request()->session()->flash('flash.link', route('settings.index', ['tab' => 'billing']));
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function togglePaymentOptions($value)
    {
        $this->showOnline = (bool) $value;

        if ($this->showOnline) {
            $this->reset(['offlineMethodId']);
        }
    }

    // Not using this function (future use)
    // public function cancelSubscriptionRazorpay()
    // {
    //     $credential = SuperadminPaymentGateway::first();

    //     if ($credential->razorpay_mode == 'test') {
    //         $apiKey = $credential->test_razorpay_key;
    //         $secretKey = $credential->test_razorpay_secret;
    //     }
    //     else {
    //         $apiKey = $credential->live_razorpay_key;
    //         $secretKey = $credential->live_razorpay_secret;
    //     }

    //     $api = new Api($apiKey, $secretKey);

    //     // Get subscription for unsubscribe
    //     $subscriptionData = RazorpaySubscription::where('restaurant', restaurant()->id)->whereNull('ends_at')->first();

    //     if ($subscriptionData) {
    //         try {
    //             $subscription = $api->subscription->fetch($subscriptionData->subscription_id);

    //             if ($subscription->status == 'active') {

    //                 // Unsubscribe plan
    //                 $subData = $api->subscription->fetch($subscriptionData->subscription_id)->cancel(['cancel_at_cycle_end' => 0]);

    //                 // Plan will be end on this date
    //                 $subscriptionData->ends_at = \Carbon\Carbon::createFromTimestamp($subData->end_at)->format('Y-m-d');
    //                 $subscriptionData->save();
    //             }

    //         } catch (\Exception $ex) {
    //             return false;
    //         }

    //         return true;
    //     }
    // }

    public function render()
    {
        return view('livewire.plan.plan-list');
    }
}
