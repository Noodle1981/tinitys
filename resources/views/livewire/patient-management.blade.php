<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public $search = '';
    public $showModal = false;
    public $editing = null;

    // Form Fields
    public $dni, $name, $birth_date, $gender, $occupation;
    public $address, $city, $province, $phone;
    public $laterality, $evolution_time;
    public $sound_type = [];
    public $comorbidities = [];
    public $mental_health_context, $medications;
    public $user_id;

    // Account Creation Fields
    public $create_user = false;
    public $user_email, $user_password;

    protected $rules = [
        'dni' => 'required|string|unique:patients,dni',
        'name' => 'required|string|min:3',
        'birth_date' => 'required|date',
        'gender' => 'required|string',
    ];

    public function with()
    {
        return [
            'patients' => Patient::where('doctor_id', Auth::id())
                ->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('dni', 'like', "%{$this->search}%");
                })
                ->orderBy('name')
                ->get(),
            'users' => User::where('role', 'patient')->get(),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Patient $patient)
    {
        $this->editing = $patient;
        $this->dni = $patient->dni;
        $this->name = $patient->name;
        $this->birth_date = $patient->birth_date ? $patient->birth_date->format('Y-m-d') : null;
        $this->gender = $patient->gender;
        $this->occupation = $patient->occupation;
        $this->address = $patient->address;
        $this->city = $patient->city;
        $this->province = $patient->province;
        $this->phone = $patient->phone;
        $this->laterality = $patient->laterality;
        $this->sound_type = $patient->sound_type ?? [];
        $this->evolution_time = $patient->evolution_time;
        $this->comorbidities = $patient->comorbidities ?? [];
        $this->mental_health_context = $patient->mental_health_context;
        $this->medications = $patient->medications;
        $this->user_id = $patient->user_id;
        
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editing) {
            $rules['dni'] = 'required|string|unique:patients,dni,' . $this->editing->id;
        }

        $this->validate($rules);

        // Account creation logic
        if ($this->create_user && !$this->user_id) {
            $this->validate([
                'user_email' => 'required|email|unique:users,email',
                'user_password' => 'required|min:6',
            ]);

            $user = User::create([
                'name' => $this->name,
                'email' => $this->user_email,
                'password' => Hash::make($this->user_password),
                'role' => 'patient',
            ]);

            $this->user_id = $user->id;
        }

        $data = [
            'dni' => $this->dni,
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'occupation' => $this->occupation,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'phone' => $this->phone,
            'laterality' => $this->laterality,
            'sound_type' => $this->sound_type,
            'evolution_time' => $this->evolution_time,
            'comorbidities' => $this->comorbidities,
            'mental_health_context' => $this->mental_health_context,
            'medications' => $this->medications,
            'user_id' => $this->user_id,
            'doctor_id' => Auth::id(),
        ];

        if ($this->editing) {
            $this->editing->update($data);
        } else {
            Patient::create($data);
        }

        // Sincronizar el nombre con el usuario asociado si lo tiene
        if ($this->user_id) {
            User::where('id', $this->user_id)->update(['name' => $this->name]);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('patient-saved');
    }

    public function delete(Patient $patient)
    {
        $patient->delete();
    }

    protected function resetForm()
    {
        $this->editing = null;
        $this->dni = $this->name = $this->birth_date = $this->gender = $this->occupation = null;
        $this->address = $this->city = $this->province = $this->phone = null;
        $this->laterality = $this->evolution_time = null;
        $this->sound_type = [];
        $this->comorbidities = [];
        $this->mental_health_context = $this->medications = $this->user_id = null;
        $this->create_user = false;
        $this->user_email = $this->user_password = null;
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Buscar por nombre o DNI..." class="max-w-xs" />
        <flux:button wire:click="create" variant="primary" icon="plus">Nuevo Paciente</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>DNI</flux:table.column>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Edad</flux:table.column>
            <flux:table.column>Lateralidad</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($patients as $patient)
                <flux:table.row :key="$patient->id">
                    <flux:table.cell class="font-medium text-slate-900">{{ $patient->dni }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->name }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->getAge() }} años</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$patient->laterality === 'Bilateral' ? 'zinc' : 'blue'">
                            {{ $patient->laterality ?? 'N/D' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="edit({{ $patient->id }})" variant="ghost" icon="pencil-square" size="sm" />
                            <flux:button wire:click="delete({{ $patient->id }})" variant="ghost" icon="trash" size="sm" color="red" confirm="¿Estás seguro de eliminar este paciente?" />
                            <a href="{{ route('patients.audiometry', $patient->id) }}" class="flex items-center gap-1.5 p-1 px-3 text-xs bg-indigo-600 rounded hover:bg-indigo-700 transition-colors font-semibold text-white">
                                <flux:icon.arrow-right-end-on-rectangle class="size-3" />
                                Ingresar Perfil
                            </a>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model="showModal" class="min-w-[40rem]">
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $editing ? 'Editar Paciente' : 'Nuevo Paciente' }}</h2>
                <p class="text-sm text-zinc-500">Completa la ficha clínica profesional.</p>
            </div>

            <div x-data="{ tab: 'basic' }" class="space-y-6">
                <div class="flex gap-2 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit">
                    <button @click="tab = 'basic'" :class="tab === 'basic' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                        Identificación
                    </button>
                    <button @click="tab = 'tinnitus'" :class="tab === 'tinnitus' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                        Tinnitus
                    </button>
                    <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all">
                        Antecedentes
                    </button>
                </div>

                <div>
                    <!-- Sección 1: Identificación -->
                    <div x-show="tab === 'basic'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">DNI / ID Único</label>
                                <flux:input wire:model="dni" placeholder="Ej: 12345678" />
                                <flux:error name="dni" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nombre Completo</label>
                                <flux:input wire:model="name" />
                                <flux:error name="name" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fecha de Nacimiento</label>
                                <flux:input type="date" wire:model="birth_date" />
                                <flux:error name="birth_date" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Género</label>
                                <flux:select wire:model="gender">
                                    <option value="">Seleccionar...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </flux:select>
                                <flux:error name="gender" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ocupación</label>
                            <flux:input wire:model="occupation" placeholder="Ej: Operario industrial" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Domicilio</label>
                                <flux:input wire:model="address" placeholder="Ej: Calle Falsa 123" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ciudad</label>
                                <flux:input wire:model="city" placeholder="Ej: Buenos Aires" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Provincia</label>
                                <flux:input wire:model="province" placeholder="Ej: Buenos Aires" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Teléfono</label>
                                <flux:input wire:model="phone" placeholder="Ej: +54 9 11 1234 5678" />
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Caracterización -->
                    <div x-show="tab === 'tinnitus'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Lateralidad</label>
                                <flux:select wire:model="laterality">
                                    <option value="">Seleccionar...</option>
                                    <option value="Derecho">Oído Derecho</option>
                                    <option value="Izquierdo">Oído Izquierdo</option>
                                    <option value="Bilateral">Bilateral</option>
                                </flux:select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Evolución</label>
                                <flux:select wire:model="evolution_time">
                                    <option value="">Seleccionar...</option>
                                    <option value="Agudo">Agudo (< 3 meses)</option>
                                    <option value="Subagudo">Subagudo (3-6 meses)</option>
                                    <option value="Crónico">Crónico (> 6 meses)</option>
                                </flux:select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tipos de Sonido (Selección múltiple)</label>
                            <div class="grid grid-cols-2 gap-2 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <flux:checkbox label="Pitido (Tonal)" wire:model="sound_type" value="Pitido" />
                                <flux:checkbox label="Siseo (Ruido)" wire:model="sound_type" value="Siseo" />
                                <flux:checkbox label="Rugido" wire:model="sound_type" value="Rugido" />
                                <flux:checkbox label="Pulsátil" wire:model="sound_type" value="Pulsátil" />
                                <flux:checkbox label="Zumbido" wire:model="sound_type" value="Zumbido" />
                                <flux:checkbox label="Grillo / Otros" wire:model="sound_type" value="Otros" />
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Antecedentes -->
                    <div x-show="tab === 'history'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Comorbilidades</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <flux:checkbox label="Hipoacusia" wire:model="comorbidities" value="Hipoacusia" />
                                <flux:checkbox label="Vértigo / Meniere" wire:model="comorbidities" value="Vértigo" />
                                <flux:checkbox label="Dolor Cervical" wire:model="comorbidities" value="Cervical" />
                                <flux:checkbox label="ATM (Mandíbula)" wire:model="comorbidities" value="ATM" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Contexto Salud Mental / Sueño</label>
                            <textarea wire:model="mental_health_context" placeholder="Ansiedad, insomnio, etc." rows="2" class="w-full border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-md text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Medicamentos</label>
                            <textarea wire:model="medications" placeholder="Fármacos actuales u ototóxicos" rows="2" class="w-full border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-md text-sm"></textarea>
                        </div>

                        <hr class="border-zinc-200 dark:border-zinc-700 my-4" />

                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Acceso al Sistema (Gemelo Digital)</h3>
                            
                            @if(!$user_id)
                                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                    <flux:checkbox label="Crear nueva cuenta para el paciente" wire:model.live="create_user" />
                                    <p class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-1 ml-6">Genera un usuario para que el paciente ingrese con su email y pueda auto-evaluarse.</p>
                                    
                                    @if($create_user)
                                        <div class="grid grid-cols-2 gap-4 mt-4">
                                            <flux:input label="Email de Acceso" wire:model="user_email" placeholder="paciente@ejemplo.com" />
                                            <flux:input label="Contraseña Temporal" type="password" wire:model="user_password" />
                                        </div>
                                    @endif
                                </div>
                            @elseif($editing && $editing->user_id == $user_id)
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-800 flex items-start gap-3">
                                    <flux:icon.check-circle class="size-5 text-emerald-500 mt-0.5" />
                                    <div>
                                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Cuenta Vinculada Activa</p>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Este paciente ya dispone de un acceso validado al sistema.</p>
                                    </div>
                                </div>
                            @endif

                            @if(!$create_user)
                                <div>
                                    <flux:select wire:model.live="user_id" label="{{ $user_id ? 'Cambiar cuenta vinculada (opcional)' : 'O vincular a una cuenta existente' }}">
                                        <option value="">No vincular a ninguna cuenta (Sin acceso)</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save" variant="primary">Guardar Paciente</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
