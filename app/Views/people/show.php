<?php
declare(strict_types=1);

$person = is_array($person ?? null) ? $person : [];
$timeline = is_array($timeline ?? null) ? $timeline : [];
$families = is_array($families ?? null) ? $families : [];
$recordForm = is_array($recordForm ?? null) ? $recordForm : [];
$personId = (int) ($person['id'] ?? 0);
$displayName = (string) (($person['full_name'] ?? '') ?: ($person['social_name'] ?? 'Sem identificacao'));
?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-success shadow-sm border-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger shadow-sm border-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="/people">Voltar para lista</a>
    <a class="btn btn-outline-primary" href="/people/edit?id=<?= $personId ?>">Editar cadastro</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Resumo da pessoa</h2>
                <dl class="row mb-0">
                    <dt class="col-5 text-secondary">Nome</dt>
                    <dd class="col-7"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">Nome social</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) (($person['social_name'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">CPF</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) (($person['cpf'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">RG</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) (($person['rg'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">Situacao</dt>
                    <dd class="col-7">
                        <?php if ((int) ($person['is_homeless'] ?? 0) === 1) : ?>
                            <span class="badge text-bg-warning">Situacao de rua</span>
                            <div class="small text-secondary mt-1"><?= htmlspecialchars((string) (($person['homeless_time'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php else : ?>
                            <span class="badge text-bg-success">Acompanhamento geral</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-5 text-secondary">Local</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) (($person['stay_location'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-secondary">Interesse trabalho</dt>
                    <dd class="col-7">
                        <?= ((int) ($person['work_interest'] ?? 0) === 1)
                            ? '<span class="badge text-bg-success">Sim</span>'
                            : '<span class="badge text-bg-light border">Nao/sem info</span>' ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Novo atendimento (ficha social)</h2>
                    <span class="badge text-bg-light border">Consentimento obrigatorio</span>
                </div>

                <form method="post" action="/people/social-records?person_id=<?= $personId ?>">
                    <input type="hidden" name="consent_text_version" value="<?= htmlspecialchars((string) ($recordForm['consent_text_version'] ?? 'v1.0'), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Vinculo opcional com familia</label>
                            <select class="form-select" name="family_id">
                                <option value="0">Sem vinculo</option>
                                <?php foreach ($families as $family) : ?>
                                    <?php $fid = (int) ($family['id'] ?? 0); ?>
                                    <option value="<?= $fid ?>" <?= ((int) ($recordForm['family_id'] ?? 0) === $fid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($family['responsible_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Necessidades imediatas</label>
                            <input class="form-control" name="immediate_needs" value="<?= htmlspecialchars((string) ($recordForm['immediate_needs'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Doencas cronicas</label>
                            <input class="form-control" name="chronic_diseases" value="<?= htmlspecialchars((string) ($recordForm['chronic_diseases'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Medicacao continua</label>
                            <input class="form-control" name="continuous_medication" value="<?= htmlspecialchars((string) ($recordForm['continuous_medication'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Uso de substancias (observacao)</label>
                            <input class="form-control" name="substance_use" value="<?= htmlspecialchars((string) ($recordForm['substance_use'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Deficiencia</label>
                            <input class="form-control" name="disability" value="<?= htmlspecialchars((string) ($recordForm['disability'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="spiritual_wants_prayer" name="spiritual_wants_prayer" value="1" <?= ((int) ($recordForm['spiritual_wants_prayer'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="spiritual_wants_prayer">Deseja oracao</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="spiritual_accepts_visit" name="spiritual_accepts_visit" value="1" <?= ((int) ($recordForm['spiritual_accepts_visit'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="spiritual_accepts_visit">Aceita visita</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Igreja</label>
                            <input class="form-control" name="church_name" value="<?= htmlspecialchars((string) ($recordForm['church_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Decisao espiritual</label>
                            <input class="form-control" name="spiritual_decision" value="<?= htmlspecialchars((string) ($recordForm['spiritual_decision'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nome no consentimento (obrigatorio)</label>
                            <input class="form-control" name="consent_name" required value="<?= htmlspecialchars((string) ($recordForm['consent_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observacoes do atendimento</label>
                            <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars((string) ($recordForm['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0 small">
                        Ao salvar, o sistema registra automaticamente a data/hora do consentimento e a versao do termo.
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-teal text-white">Registrar atendimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Linha do tempo de atendimentos</h2>
            <span class="badge text-bg-light border"><?= count($timeline) ?> registro(s)</span>
        </div>

        <?php if (empty($timeline)) : ?>
            <div class="text-secondary">Nenhum atendimento social registrado para esta pessoa.</div>
        <?php else : ?>
            <div class="vstack gap-3">
                <?php foreach ($timeline as $record) : ?>
                    <div class="border rounded-3 p-3 bg-light-subtle">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">
                                Atendimento #<?= (int) ($record['id'] ?? 0) ?>
                                <span class="text-secondary fw-normal">em <?= htmlspecialchars((string) ($record['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="small text-secondary">
                                por <?= htmlspecialchars((string) (($record['created_by_name'] ?? '') ?: 'usuario'), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>

                        <div class="row g-2 small mb-2">
                            <div class="col-12 col-md-4">
                                <strong>Familia:</strong>
                                <?= htmlspecialchars((string) (($record['family_name'] ?? '') ?: 'Sem vinculo'), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="col-12 col-md-4">
                                <strong>Consentimento:</strong>
                                <?= htmlspecialchars((string) ($record['consent_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="col-12 col-md-4">
                                <strong>Termo:</strong>
                                <?= htmlspecialchars((string) ($record['consent_text_version'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                / <?= htmlspecialchars((string) ($record['consent_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>

                        <?php if (!empty($record['immediate_needs'])) : ?>
                            <div class="small mb-1"><strong>Necessidades:</strong> <?= htmlspecialchars((string) $record['immediate_needs'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($record['notes'])) : ?>
                            <div class="small"><strong>Observacoes:</strong> <?= nl2br(htmlspecialchars((string) $record['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

