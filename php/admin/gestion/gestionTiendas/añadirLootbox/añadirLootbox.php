<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}
$admin = true;

// Obtener items de la tienda (excepto lootboxes)
$resItems = mysqli_query($conexion, "SELECT id_item, nombre FROM Tienda_Items WHERE tipo IN ('avatar','marco','fondo')");
$items = mysqli_fetch_all($resItems, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Añadir Lootbox</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
</head>
<body>

<header>
    <div class="tituloWeb">
        <img src="../../../../../media/logoPlatino.png" width="40px">
        <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>
    <nav>
        <ul>
            <li><a href="../gestionTienda.php">Volver al panel de gestión</a></li>
        </ul>
    </nav>
    <?php if(isset($_SESSION['tag'])) : ?>
        <a class="tag" href="../../../../user/perfiles/perfilSesion.php">
            <?php echo htmlspecialchars($_SESSION['tag']); ?>
        </a>
    <?php endif; ?>
</header>

<div class="central">
    <h1>Añadir Lootbox</h1>
</div>

<div id="addLootboxPage" class="admin-container">
    <form id="formLootbox" method="POST" action="procesarAñadirLootbox.php">
        <label>Nombre de la Lootbox:</label>
        <input type="text" name="nombre" required>

        <label>Precio:</label>
        <input type="number" name="precio" min="0" required>

        <label>Imagen de la lootbox:</label>
        <select name="imagen" required>
            <option value="">Selecciona una imagen</option>
            <option value="lootbox_default.png">Caja clásica</option>
            <option value="lootbox_plata.png">Caja plateada</option>
            <option value="lootbox_oro.png">Caja dorada</option>
            <option value="lootbox_legendaria.png">Caja legendaria</option>
        </select>

        <div class="container">
            <div class="items-disponibles">
                <h3>Items disponibles</h3>
                <ul id="itemsPool">
                    <?php foreach ($items as $item): ?>
                        <li draggable="true" data-id="<?php echo $item['id_item']; ?>">
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lootbox">
                <h3>Items en la Lootbox</h3>
                <ul id="lootboxItems"></ul>
                <div>
                    <strong>Total probabilidad:</strong> <span id="totalProb">0%</span>
                </div>
                <button type="submit">Guardar Lootbox</button>
            </div>
        </div>
    </form>
</div>

<script>
const itemsPoolUL = document.querySelectorAll('#addLootboxPage #itemsPool li');
const lootboxUL = document.getElementById('lootboxItems');
const totalProbSpan = document.getElementById('totalProb');
let totalProb = 0;

// Arrastrar y soltar items
itemsPoolUL.forEach(li => {
    li.setAttribute('draggable', true);
    li.addEventListener('dragstart', e => {
        const liTarget = e.target.closest('li');
        if (!liTarget) return;
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', liTarget.dataset.id);
    });
});

lootboxUL.addEventListener('dragover', e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
});

lootboxUL.addEventListener('drop', e => {
    e.preventDefault();
    const id = e.dataTransfer.getData('text/plain');
    if (!id) return;
    if ([...lootboxUL.children].some(li => li.dataset.id === id)) return;
    const originalItem = document.querySelector(`#addLootboxPage #itemsPool li[data-id='${id}']`);
    if (!originalItem) return;
    const li = document.createElement('li');
    li.dataset.id = id;
    li.innerHTML = `
        <span class="item-name">${originalItem.textContent.trim()}</span>
        - Probabilidad: <input type="number" min="1" max="100" value="10" class="prob"> %
        <button type="button" class="remove">X</button>
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

lootboxUL.addEventListener('input', e => {
    if (e.target.classList.contains('prob')) updateTotal();
});

function updateTotal() {
    let total = 0;
    lootboxUL.querySelectorAll('.prob').forEach(input => total += parseInt(input.value) || 0);
    totalProb = total;
    totalProbSpan.textContent = `${total}%`;
}

document.getElementById('formLootbox').addEventListener('submit', e => {
    if (totalProb > 100) {
        alert('La suma de probabilidades no puede superar 100%');
        e.preventDefault();
        return;
    }

    const oldInputs = e.target.querySelectorAll('input[name^="items"]');
    oldInputs.forEach(i => i.remove());

    [...lootboxUL.children].forEach((li, index) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = `items[${index}][id_item]`;
        idInput.value = li.dataset.id;

        const probInput = document.createElement('input');
        probInput.type = 'hidden';
        probInput.name = `items[${index}][probabilidad]`;
        probInput.value = li.querySelector('.prob').value;

        e.target.appendChild(idInput);
        e.target.appendChild(probInput);
    });
});
</script>

</body>
</html>