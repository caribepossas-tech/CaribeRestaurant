<?php

namespace App\Livewire\Forms;

use App\Models\Role;
use App\Models\User;
use App\Notifications\StaffWelcomeEmail;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AddStaff extends Component
{

    use LivewireAlert;

    public $roles;
    public $memberName;
    public $memberEmail;
    public $memberRole;
    public $memberPassword;
    public $passwordMode = 'manual';

    public function mount()
    {
        $this->roles = Role::where('display_name', '<>', 'Super Admin')->get();
        $this->memberRole = $this->roles->first()->name;
    }

    public function submitForm()
    {
        $this->validate([
            'memberName' => 'required',
            'memberPassword' => 'required_if:passwordMode,manual',
            'memberEmail' => 'required|email|unique:users,email'
        ]);

        $password = $this->passwordMode == 'manual' ? $this->memberPassword : str()->random(16);

        $user = User::create([
            'name' => $this->memberName,
            'email' => $this->memberEmail,
            'password' => bcrypt($password),
        ]);

        $user->assignRole($this->memberRole);

        if ($this->passwordMode == 'manual') {
            $user->notify(new StaffWelcomeEmail($user->restaurant, $password));
        } else {
            $token = \Password::createToken($user);
            $user->notify(new \App\Notifications\StaffSetPasswordEmail($user->restaurant, $token));
        }

        // Reset the value
        $this->reset(['memberName', 'memberEmail', 'memberRole', 'memberPassword', 'passwordMode']);
        $this->memberRole = $this->roles->first()->name;

        $this->dispatch('hideAddStaff');

        $this->alert('success', __('messages.memberAdded'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close')
        ]);
    }

    public function render()
    {
        return view('livewire.forms.add-staff');
    }

}
