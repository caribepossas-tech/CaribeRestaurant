<?php

namespace App\Livewire\Shop;

use App\Models\Branch;
use App\Models\Order;
use Livewire\Component;

class OrderSuccess extends Component
{

    public $id;
    public $order;
    public $restaurant;
    public $shopBranch;

    public function mount()
    {
        $this->order = Order::with('taxes.tax', 'items.menuItem')->where('id', $this->id)->firstOrFail();

        if (is_null(customer()) && $this->restaurant->customer_login_required)
        {
            return $this->redirect(route('home'));
        }

        if (request()->branch && request()->branch != '') {
            $this->shopBranch = Branch::find(request()->branch);

        } else {
            $this->shopBranch = $this->restaurant->branches->first();
        }
    }

    public function render()
    {
        // Re-fetch order on every poll cycle so status updates are visible to the customer
        $this->order = Order::with('taxes.tax', 'items.menuItem', 'items.menuItemVariation', 'items.modifierOptions')
            ->where('id', $this->id)
            ->firstOrFail();

        return view('livewire.shop.order-success');
    }

}
