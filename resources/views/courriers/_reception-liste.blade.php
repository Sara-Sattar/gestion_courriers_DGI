<div class="reception-stats dashboard-stats">
    <article class="stat-card stat-card--primary">
        <div class="stat-card-icon"><i class="bi bi-inbox-fill"></i></div>
        <div class="stat-card-info">
            <span class="stat-card-label">À confirmer aujourd'hui</span>
            <span class="stat-card-value">{{ $courriersAujourdhui->count() }}</span>
            <span class="stat-card-hint">Arrivés dans votre unité aujourd'hui</span>
        </div>
    </article>
    <article class="stat-card stat-card--info">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-info">
            <span class="stat-card-label">En instance</span>
            <span class="stat-card-value">{{ $courriersEnInstance->count() }}</span>
            <span class="stat-card-hint">En attente depuis avant aujourd'hui</span>
        </div>
    </article>
</div>

{{-- Courriers du jour --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white px-4 py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-calendar-day me-2 text-primary"></i>À confirmer — aujourd'hui
        </h5>
        <p class="text-muted small mb-0 mt-1">Courriers au statut Déposé, arrivés dans votre unité aujourd'hui (dépôt ou réaffectation).</p>
    </div>
    @include('courriers._reception-table', [
        'courriers' => $courriersAujourdhui,
        'formId' => 'reception-form-today',
        'selectAllId' => 'select-all-today',
        'emptyMessage' => 'Aucun courrier déposé aujourd\'hui en attente de réception.',
        'mode' => 'attente',
        'showReceptionDate' => false,
    ])
</div>

{{-- En instance --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white px-4 py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-hourglass-split me-2 text-info"></i>En instance de réception
        </h5>
        <p class="text-muted small mb-0 mt-1">Courriers déposés ou réaffectés avant aujourd'hui, toujours en attente de confirmation.</p>
    </div>
    @include('courriers._reception-table', [
        'courriers' => $courriersEnInstance,
        'formId' => 'reception-form-pending',
        'selectAllId' => 'select-all-pending',
        'emptyMessage' => 'Aucun courrier en instance de réception.',
        'mode' => 'attente',
        'showReceptionDate' => false,
    ])
</div>
