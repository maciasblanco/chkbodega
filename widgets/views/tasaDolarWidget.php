<?php
// widgets/views/tasaDolarWidget.php

use yii\helpers\Html;
use yii\helpers\Url;

$montoSemanal = \app\models\AportesSemanales::MONTO_SEMANAL;
$montoSemanalBs = $tasaActual * $montoSemanal;
?>

<div class="tasa-dolar-widget card <?= $compact ? 'compact' : '' ?>">
    <div class="card-header bg-info text-white py-2">
        <small>
            <i class="fas fa-dollar-sign me-1"></i> 
            Tasa del Dólar
            <?php if (!$compact): ?>
                <span class="float-end">
                    <i class="fas fa-calendar-day"></i>
                </span>
            <?php endif; ?>
        </small>
    </div>
    <div class="card-body p-2">
        <?php if ($tasaActual > 0): ?>
            <div class="text-center mb-2">
                <h5 class="text-success mb-1 fw-bold">Bs. <?= number_format($tasaActual, 2) ?></h5>
                <small class="text-muted">Por $1 USD</small>
            </div>
            
            <?php if (!$compact): ?>
                <div class="small text-center mb-2 border-top pt-2">
                    <strong class="d-block mb-1">Aporte Semanal:</strong>
                    <span class="text-dark">
                        $<?= number_format($montoSemanal, 2) ?> = 
                        Bs. <?= number_format($montoSemanalBs, 2) ?>
                    </span>
                </div>

                <?php if ($showCalculator): ?>
                <div class="mini-calculadora border-top pt-2 mt-2">
                    <small class="text-muted d-block mb-1">
                        <i class="fas fa-calculator me-1"></i>Calculadora
                    </small>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control form-control-sm" 
                               id="mini-monto-dolares" 
                               value="1.00" 
                               step="0.01" 
                               min="0.01">
                    </div>
                    <div class="resultado text-center">
                        <small class="text-dark fw-bold" id="mini-resultado">
                            Bs. <?= number_format($tasaActual, 2) ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center text-warning py-2">
                <i class="fas fa-exclamation-triangle fa-lg mb-2"></i><br>
                <small>Tasa no configurada</small>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-2">
            <?= Html::a(
                $compact ? '<i class="fas fa-cog"></i>' : '<i class="fas fa-cog me-1"></i> Configurar', 
                ['/tasa-dolar/index'], 
                [
                    'class' => $compact ? 'btn btn-sm btn-outline-primary' : 'btn btn-sm btn-outline-primary w-100',
                    'title' => 'Configurar tasa del dólar'
                ]
            ) ?>
        </div>
    </div>
</div>

<?php if ($showCalculator && !$compact): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const miniMontoInput = document.getElementById('mini-monto-dolares');
    const miniResultado = document.getElementById('mini-resultado');
    const tasaActual = <?= $tasaActual ?>;

    function calcularMiniConversion() {
        const montoDolares = parseFloat(miniMontoInput.value) || 0;
        const total = montoDolares * tasaActual;
        
        miniResultado.textContent = 'Bs. ' + total.toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    if (miniMontoInput && miniResultado) {
        miniMontoInput.addEventListener('input', calcularMiniConversion);
        calcularMiniConversion(); // Calcular inicialmente
    }
});
</script>
<?php endif; ?>