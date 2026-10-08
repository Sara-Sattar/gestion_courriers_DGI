@if ($errors->any())
    <div class="alert alert-danger border-0 d-flex gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0" aria-hidden="true"></i>
        <div>
            <p class="fw-semibold mb-1">
                Le formulaire contient {{ $errors->count() }} {{ $errors->count() > 1 ? 'erreurs' : 'erreur' }}.
            </p>
            <p class="mb-0 small">Corrigez les champs signalés en rouge ci-dessous.</p>
        </div>
    </div>
@endif
