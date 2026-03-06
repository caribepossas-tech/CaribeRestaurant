<?php

namespace App\Livewire\Settings;

use App\Models\POSPaymentMethod;
use App\Models\PaymentGatewayCredential;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Helper\Files;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class PaymentSettings extends Component
{

    use LivewireAlert, WithFileUploads;

    public $razorpaySecret;
    public $razorpayKey;
    public $razorpayStatus;
    public $isRazorpayEnabled;
    public $isStripeEnabled;
    public $offlinePaymentMethod;
    public $paymentGateway;
    public $stripeSecret;
    public $activePaymentSetting = 'razorpay';
    public $stripeKey;
    public bool $stripeStatus;
    public bool $enableForDineIn;
    public bool $enableForDelivery;
    public bool $enableForPickup;
    public $enableCashPayment = false;
    public $enableQrPayment = false;
    public $paymentDetails;
    public $qrCodeImage;
    public $isofflinepaymentEnabled;
    public bool $enablePayViaCash;
    public bool $enableOfflinePayment;
    public $bankName;
    public $bankAccountDetails;

    public $settings;
    public $posPaymentMethods;
    public $offlinePaymentMethods;
    public $newMethodName = '';
    public $newBankName = '';
    public $newBankAccountDetails = '';
    public $newShowInShop = false;
    public $editingMethodId = null;
    public $editingMethodName = '';
    public $editingBankName = '';
    public $editingBankAccountDetails = '';
    public $editingShowInShop = false;
    public $wompiStatus = false;
    public $isWompiEnabled = false;

    public function mount()
    {
        $this->paymentGateway = PaymentGatewayCredential::first() ?? new PaymentGatewayCredential(['restaurant_id' => restaurant()->id ?? null]);
        $this->setCredentials();
        $this->fetchPOSPaymentMethods();
    }

    public function activeSetting($tab)
    {
        $this->activePaymentSetting = $tab;
        $this->setCredentials();
    }

    private function setCredentials()
    {
        if (!$this->paymentGateway || !$this->paymentGateway->exists) {
            $this->isRazorpayEnabled = false;
            $this->isStripeEnabled = false;
            $this->razorpayStatus = false;
            $this->stripeStatus = false;
            $this->wompiStatus = false;
            $this->isWompiEnabled = false;
            $this->enableForDineIn = false;
            $this->enableForDelivery = false;
            $this->enableForPickup = false;
            $this->enableOfflinePayment = false;
            $this->enableQrPayment = false;
            return;
        }

        $this->razorpayKey = $this->paymentGateway->razorpay_key;
        $this->razorpaySecret = $this->paymentGateway->razorpay_secret;
        $this->razorpayStatus = (bool)$this->paymentGateway->razorpay_status;

        $this->stripeKey = $this->paymentGateway->stripe_key;
        $this->stripeSecret = $this->paymentGateway->stripe_secret;
        $this->stripeStatus = (bool)$this->paymentGateway->stripe_status;

        $this->isRazorpayEnabled = $this->paymentGateway->razorpay_status;
        $this->isStripeEnabled = $this->paymentGateway->stripe_status;

        $this->enableForDineIn = $this->paymentGateway->is_dine_in_payment_enabled;
        $this->enableForDelivery = $this->paymentGateway->is_delivery_payment_enabled;
        $this->enableForPickup = $this->paymentGateway->is_pickup_payment_enabled;

        $this->enableOfflinePayment = (bool)$this->paymentGateway->is_offline_payment_enabled;
        $this->enableQrPayment = (bool)$this->paymentGateway->is_qr_payment_enabled;
        $this->paymentDetails = $this->paymentGateway->offline_payment_detail;
        $this->qrCodeImage = $this->paymentGateway->qr_code_image_url;
        $this->enablePayViaCash = (bool)$this->paymentGateway->is_cash_payment_enabled;
        $this->bankName = $this->paymentGateway->bank_name;
        $this->bankAccountDetails = $this->paymentGateway->bank_account_details;
    }

    private function fetchPOSPaymentMethods()
    {
        $this->posPaymentMethods = POSPaymentMethod::where('restaurant_id', restaurant()->id)
            ->where('type', 'pos')
            ->get();
            
        $this->offlinePaymentMethods = POSPaymentMethod::where('restaurant_id', restaurant()->id)
            ->where('type', 'offline')
            ->get();
    }

    public function addPOSPaymentMethod()
    {
        $restaurant = restaurant();
        if (!$restaurant) {
            return;
        }

        $this->validate([
            'newMethodName' => 'required|string|max:255',
            'newBankName' => 'nullable|string|max:255',
            'newBankAccountDetails' => 'nullable|string',
            'newShowInShop' => 'boolean'
        ]);

        POSPaymentMethod::create([
            'restaurant_id' => $restaurant->id,
            'type' => $this->activePaymentSetting === 'pos' ? 'pos' : 'offline',
            'name' => $this->newMethodName,
            'bank_name' => $this->newBankName,
            'bank_account_details' => $this->newBankAccountDetails,
            'show_in_shop' => $this->newShowInShop,
            'status' => 'active'
        ]);

        $this->newMethodName = '';
        $this->newBankName = '';
        $this->newBankAccountDetails = '';
        $this->newShowInShop = false;
        $this->updatePaymentStatus();
        $this->alertSuccess();
    }

    public function deletePOSPaymentMethod($id)
    {
        POSPaymentMethod::where('id', $id)->delete();
        $this->updatePaymentStatus();
        $this->alertSuccess();
    }

    public function editPOSPaymentMethod($id)
    {
        $method = POSPaymentMethod::where('id', $id)->first();
        if ($method) {
            $this->editingMethodId = $id;
            $this->editingMethodName = $method->name;
            $this->editingBankName = $method->bank_name;
            $this->editingBankAccountDetails = $method->bank_account_details;
            $this->editingShowInShop = (bool)$method->show_in_shop;
        }
    }

    public function updatePOSPaymentMethod()
    {
        $this->validate([
            'editingMethodName' => 'required|string|max:255',
            'editingBankName' => 'nullable|string|max:255',
            'editingBankAccountDetails' => 'nullable|string',
            'editingShowInShop' => 'boolean'
        ]);

        POSPaymentMethod::where('id', $this->editingMethodId)
            ->where('restaurant_id', restaurant()->id)
            ->update([
                'name' => $this->editingMethodName,
                'bank_name' => $this->editingBankName,
                'bank_account_details' => $this->editingBankAccountDetails,
                'show_in_shop' => $this->editingShowInShop,
            ]);

        $this->cancelEdit();
        $this->fetchPOSPaymentMethods();
        $this->updatePaymentStatus();
        $this->alertSuccess();
    }

    public function cancelEdit()
    {
        $this->editingMethodId = null;
        $this->editingMethodName = '';
        $this->editingBankName = '';
        $this->editingBankAccountDetails = '';
        $this->editingShowInShop = false;
    }

    public function toggleMethodStatus($id)
    {
        $method = POSPaymentMethod::where('id', $id)->first();
        if ($method) {
            $method->status = $method->status === 'active' ? 'inactive' : 'active';
            $method->save();
            $this->updatePaymentStatus();
        }
    }

    public function toggleShowInShop($id)
    {
        $method = POSPaymentMethod::where('id', $id)->first();
        if ($method) {
            $method->show_in_shop = !$method->show_in_shop;
            $method->save();
            $this->updatePaymentStatus();
        }
    }

    public function submitFormServiceSpecific()
    {
        $this->paymentGateway->update([
            'is_dine_in_payment_enabled' => $this->enableForDineIn,
            'is_delivery_payment_enabled' => $this->enableForDelivery,
            'is_pickup_payment_enabled' => $this->enableForPickup,
        ]);
        $this->updatePaymentStatus();
        $this->alertSuccess();
    }

    public function submitFormRazorpay()
    {
        $this->validate([
            'razorpaySecret' => 'required_if:razorpayStatus,true',
            'razorpayKey' => 'required_if:razorpayStatus,true',
        ]);

        if ($this->saveRazorpaySettings() === 0) {
            $this->updatePaymentStatus();
            $this->alertSuccess();
        }
    }

    public function submitFormStripe()
    {
        $this->validate([
            'stripeSecret' => 'required_if:stripeStatus,true',
            'stripeKey' => 'required_if:stripeStatus,true',
        ]);

        if ($this->saveStripeSettings() === 0) {
            $this->updatePaymentStatus();
            $this->alertSuccess();
        }
    }

    public function submitFormOffline()
    {
        $rules = [
            'enableOfflinePayment' => 'required|boolean',
            'enableQrPayment' => 'required|boolean',
            'enablePayViaCash' => 'required|boolean'
        ];

        if ($this->enableOfflinePayment) {
            $rules['paymentDetails'] = 'required|string|max:1000';
        }

        if ($this->enableQrPayment && !$this->paymentGateway->qr_code_image) {
            $rules['qrCodeImage'] = 'required|image|max:1024';
        }

        $this->validate($rules);

        // Upload QR code image if enabled and valid
        if ($this->enableQrPayment && is_object($this->qrCodeImage) && $this->qrCodeImage->isValid()) {
            $this->qrCodeImage = Files::uploadLocalOrS3($this->qrCodeImage, PaymentGatewayCredential::QR_CODE_FOLDER, width: 150);
        } else {
            $this->qrCodeImage = $this->paymentGateway->qr_code_image;
        }


        $updateData = [
            'is_offline_payment_enabled' => $this->enableOfflinePayment,
            'offline_payment_detail' => $this->enableOfflinePayment ? $this->paymentDetails : $this->paymentDetails,
            'is_qr_payment_enabled' => $this->enableQrPayment,
            'qr_code_image' => $this->qrCodeImage,
            'is_cash_payment_enabled' => $this->enablePayViaCash,
            'bank_name' => $this->bankName,
            'bank_account_details' => $this->bankAccountDetails,
        ];

        $this->paymentGateway->update($updateData);

        $this->updatePaymentStatus();
        $this->alertSuccess();
    }

    private function saveRazorpaySettings()
    {
        if (!$this->razorpayStatus) {
            $this->paymentGateway->update([
                'razorpay_status' => $this->razorpayStatus,
            ]);
            return 0;
        }

        try {
            $response = Http::withBasicAuth($this->razorpayKey, $this->razorpaySecret)
                ->get('https://api.razorpay.com/v1/contacts');

            if ($response->successful()) {
                $this->paymentGateway->update([
                    'razorpay_key' => $this->razorpayKey,
                    'razorpay_secret' => $this->razorpaySecret,
                    'razorpay_status' => $this->razorpayStatus,
                ]);
                return 0;
            }

            $this->addError('razorpayKey', 'Invalid Razorpay key or secret.');
        } catch (\Exception $e) {
            $this->addError('razorpayKey', 'Error: ' . $e->getMessage());
        }

        return 1;
    }

    private function saveStripeSettings()
    {

        if (!$this->stripeStatus) {
            $this->paymentGateway->update([
                'stripe_status' => $this->stripeStatus,
            ]);
            return 0;
        }

        try {
            $response = Http::withToken($this->stripeSecret)
                ->get('https://api.stripe.com/v1/customers');

            if ($response->successful()) {
                $this->paymentGateway->update([
                    'stripe_key' => $this->stripeKey,
                    'stripe_secret' => $this->stripeSecret,
                    'stripe_status' => $this->stripeStatus,
                ]);
                return 0;
            }

            $this->addError('stripeKey', 'Invalid Stripe key or secret.');
        } catch (\Exception $e) {
            $this->addError('stripeKey', 'Error: ' . $e->getMessage());
        }

        return 1;
    }

    public function updatePaymentStatus()
    {
        $this->setCredentials();
        $this->dispatch('settingsUpdated');
        session()->forget('paymentGateway');
    }

    public function alertSuccess()
    {
        $this->alert('success', __('messages.settingsUpdated'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close'),
        ]);
    }

    public function render()
    {
        return view('livewire.settings.payment-settings');
    }
}
