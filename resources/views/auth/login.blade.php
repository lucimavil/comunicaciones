<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background:#eaf3f8;">
        <div class="card shadow-sm border-0" style="width: 450px; border-radius: 12px;">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                   <img src="{{ asset('img/logo.png') }}" alt="Logo">
                    <h4 class="fw-bold mb-0 mt-2">Sistema de Comunicaciones por WhatsApp</h4>
                    <div class="text-muted">Hospital Ramón Carrillo</div>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input 
                            type="text" 
                            name="email" 
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            style="height: 42px;"
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror"
                            required
                            style="height: 42px;"
                        >

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Ingresar
                    </button>
                </form>

                <div class="text-center text-muted small mt-4">
                    Plataforma de Comunicación Institucional
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>