<?php

namespace App\Livewire\Patients;

use App\Livewire\Forms\PatientForm;
use App\Models\Patient;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

class PatientManagement extends Component
{
    use WithPagination;

    public PatientForm $form;

    public $search = '';
    public $showModal = false;
    
    // User Creation Fields
    public $create_user = false;
    public $user_email, $user_password;

    protected $listeners = ['patient-saved' => 'render'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->form->reset();
        $this->create_user = false;
        $this->user_email = $this->user_password = null;
        $this->showModal = true;
    }

    public function edit(Patient $patient)
    {
        $this->form->setPatient($patient);
        $this->create_user = false;
        $this->user_email = $this->user_password = null;
        $this->showModal = true;
    }

    public function save()
    {
        // Handle User Creation if requested
        if ($this->create_user && !$this->form->user_id) {
            $this->validate([
                'user_email' => 'required|email|unique:users,email',
                'user_password' => 'required|min:6',
            ]);

            $user = User::create([
                'name' => $this->form->name,
                'email' => $this->user_email,
                'password' => Hash::make($this->user_password),
                'role' => 'patient',
            ]);

            $this->form->user_id = $user->id;
        }

        $this->form->store();

        if ($this->form->user_id) {
            User::where('id', $this->form->user_id)->update(['name' => $this->form->name]);
        }

        $this->showModal = false;
        
        Flux::toast(
            heading: 'Operación Exitosa',
            text: 'Información del paciente actualizada correctamente.',
            variant: 'success'
        );
    }

    public function delete(Patient $patient)
    {
        $patient->delete();
        
        Flux::toast(
            heading: 'Paciente Eliminado',
            text: 'El registro ha sido removido de la base de datos.',
            variant: 'success'
        );
    }

    public function render()
    {
        return view('livewire.patients.index', [
            'patients' => Patient::where('doctor_id', Auth::id())
                ->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('dni', 'like', "%{$this->search}%");
                })
                ->orderBy('name')
                ->paginate(10),
            'users' => User::where('role', 'patient')->get(),
        ]);
    }
}
