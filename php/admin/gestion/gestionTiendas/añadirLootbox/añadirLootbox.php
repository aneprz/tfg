<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}
$admin = true;

$resItems = mysqli_query($conexion, "SELECT id_item, nombre FROM Tienda_Items WHERE tipo IN ('avatar','marco','fondo')");
$items = mysqli_fetch_all($resItems, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Cajas de Evento</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <style>
        .buscador-items { width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #444; background: #222; color: #fff; }
        .color-picker-container { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .color-picker { width: 50px; height: 50px; padding: 0; border: none; border-radius: 5px; cursor: pointer; }
        .puntos-automaticos { background: #2c3440; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #4aa3f0; }
        .puntos-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #444; }
        .puntos-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .puntos-item input { width: 60px; padding: 5px; text-align: center; }
    </style>
</head>
<body>

<header>
    <div class="tituloWeb">
        <img src="../../../../../media/logoPlatino.png" width="40px">
        <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>
    <nav><ul><li><a href="../gestionTienda.php">Volver al panel</a></li></ul></nav>
</header>

<div class="central">
    <h1>Crear Caja de Evento</h1>
    <p>La lógica de puntos se calcula sola. Solo añade entre 3 y 5 cosméticos.</p>
</div>

<div id="addLootboxPage" class="admin-container">
    <form id="formLootbox" method="POST" action="procesarAñadirLootbox.php" enctype="multipart/form-data">
        <label>Nombre del Evento / Caja:</label>
        <input type="text" name="nombre" required>

        <label>Precio de la caja (Puntos):</label>
        <input type="number" id="precioInput" name="precio" min="10" required>

        <div class="color-picker-container">
            <div>
                <label>Color del Neón:</label>
                <input type="color" name="color_neon" class="color-picker" value="#00ffcc">
            </div>
            <div style="flex-grow: 1;">
                <label>Sube la imagen de la caja (PNG/JPG):</label>
                <input type="file" name="imagen_caja" accept="image/*" required style="background: #222; padding: 10px; border-radius: 5px; width: 100%;">
            </div>
        </div>

        <div class="container">
            <div class="items-disponibles">
                <h3>Cosméticos (Arrastra 3 a 5)</h3>
                <input type="text" id="buscadorItems" class="buscador-items" placeholder="Buscar cosmético...">
                <ul id="itemsPool" style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($items as $item): ?>
                        <li draggable="true" data-id="<?php echo $item['id_item']; ?>" class="item-arrastrable">
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lootbox">
                <div class="puntos-automaticos">
                    <h3 style="margin-top: 0; color: #4aa3f0;">Premios de Puntos (Automáticos)</h3>
                    
                    <div class="puntos-item">
                        <span>📉 Consuelo (<span id="txt-consuelo">0</span> Pts)</span>
                        <div><input type="number" name="prob_consuelo" class="prob" value="45" min="1" max="100"> %</div>
                        <input type="hidden" name="pts_consuelo" id="val-consuelo" value="0">
                    </div>

                    <div class="puntos-item">
                        <span>📈 Ganancia (<span id="txt-ganancia">0</span> Pts)</span>
                        <div><input type="number" name="prob_ganancia" class="prob" value="15" min="1" max="100"> %</div>
                        <input type="hidden" name="pts_ganancia" id="val-ganancia" value="0">
                    </div>

                    <div class="puntos-item">
                        <span>🏆 JACKPOT (<span id="txt-jackpot">0</span> Pts)</span>
                        <div><input type="number" name="prob_jackpot" class="prob" value="2" min="1" max="100"> %</div>
                        <input type="hidden" name="pts_jackpot" id="val-jackpot" value="0">
                    </div>
                </div>

                <h3>Cosméticos Arrastrados (<span id="contadorCosmeticos">0</span>/5)</h3>
                <ul id="lootboxItems" style="min-height: 100px; border: 1px dashed #666; padding: 10px;"></ul>
                
                <div style="margin-top: 20px; font-size: 1.2rem; background: #111; padding: 15px; border-radius: 5px;">
                    <strong>Total Probabilidad:</strong> <span id="totalProb" style="color: #f0c330;">62%</span>
                </div>
                <button type="submit" style="background: #f0c330; color: black; font-weight: bold; margin-top: 15px; width: 100%; font-size: 1.1rem; padding: 15px;">Publicar Caja de Evento</button>
            </div>
        </div>
    </form>
</div>

<script>
// Cálculo automático de puntos
document.getElementById('precioInput').addEventListener('input', function(e) {
    let precio = parseInt(e.target.value) || 0;
    
    let consuelo = Math.round(precio * 0.50);
    let ganancia = Math.round(precio * 1.50);
    let jackpot = Math.round(precio * 7.00);

    document.getElementById('txt-consuelo').innerText = consuelo;
    document.getElementById('val-consuelo').value = consuelo;

    document.getElementById('txt-ganancia').innerText = ganancia;
    document.getElementById('val-ganancia').value = ganancia;

    document.getElementById('txt-jackpot').innerText = jackpot;
    document.getElementById('val-jackpot').value = jackpot;
});

// Buscador
document.getElementById('buscadorItems').addEventListener('input', function(e) {
    const texto = e.target.value.toLowerCase();
    document.querySelectorAll('#itemsPool .item-arrastrable').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(texto) ? 'block' : 'none';
    });
});

// Arrastrar y soltar
const itemsPoolUL = document.querySelectorAll('#addLootboxPage #itemsPool li');
const lootboxUL = document.getElementById('lootboxItems');
const totalProbSpan = document.getElementById('totalProb');
const contadorSpan = document.getElementById('contadorCosmeticos');

itemsPoolUL.forEach(li => {
    li.setAttribute('draggable', true);
    li.addEventListener('dragstart', e => { e.dataTransfer.setData('text/plain', e.target.dataset.id); });
});

lootboxUL.addEventListener('dragover', e => e.preventDefault());

lootboxUL.addEventListener('drop', e => {
    e.preventDefault();
    if (lootboxUL.children.length >= 5) {
        alert("¡Máximo 5 cosméticos alcanzado!");
        return;
    }
    const id = e.dataTransfer.getData('text/plain');
    if ([...lootboxUL.children].some(li => li.dataset.id === id)) return;
    
    const originalItem = document.querySelector(`#addLootboxPage #itemsPool li[data-id='${id}']`);
    if (!originalItem) return;
    
    const li = document.createElement('li');
    li.dataset.id = id;
    li.innerHTML = `
        <span class="item-name">${originalItem.textContent.trim()}</span>
        <div><input type="number" min="1" max="100" value="5" class="prob"> %</div>
        <button type="button" class="remove" style="background:red; color:white; border:none; cursor:pointer;">X</button>
    `;
    lootboxUL.appendChild(li);
    updateTotal();
});

lootboxUL.addEventListener('click', e => {
    if (e.target.classList.contains('remove')) {
        e.target.parentElement.remove();
        updateTotal();
    }
});

// Actualizar totales al escribir
document.getElementById('formLootbox').addEventListener('input', e => {
    if (e.target.classList.contains('prob')) updateTotal();
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.prob').forEach(input => total += parseInt(input.value) || 0);
    totalProbSpan.textContent = `${total}%`;
    totalProbSpan.style.color = total === 100 ? '#2ecc71' : '#e74c3c';
    contadorSpan.textContent = lootboxUL.children.length;
}

// Validación Final antes de enviar
document.getElementById('formLootbox').addEventListener('submit', e => {
    let numCosmeticos = lootboxUL.children.length;
    let total = parseInt(totalProbSpan.textContent);

    if (numCosmeticos < 3 || numCosmeticos > 5) {
        alert('Debes añadir entre 3 y 5 cosméticos. Tienes: ' + numCosmeticos);
        e.preventDefault();
        return;
    }

    if (total !== 100) {
        alert('La suma TOTAL de todas las probabilidades tiene que ser exactamente 100%. Tienes: ' + total + '%');
        e.preventDefault();
        return;
    }

    // Preparar inputs ocultos para PHP
    [...lootboxUL.children].forEach((li, index) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = `cosmeticos[${index}][id_item]`;
        idInput.value = li.dataset.id;

        const probInput = document.createElement('input');
        probInput.type = 'hidden';
        probInput.name = `cosmeticos[${index}][probabilidad]`;
        probInput.value = li.querySelector('.prob').value;

        e.target.appendChild(idInput);
        e.target.appendChild(probInput);
    });
});
</script>
</body>
</html>