<?php
# ==========================================
# 1. CONEXIÓN Y REGISTRO EN POSTGRESQL
# ==========================================
try {
    $conexion = new PDO('pgsql:host=dpg-d8f393d53gjs739kqmug-a.ohio-postgres.render.com;dbname=sena_dkhe','sena_dkhe_user','I75YJRHQ1iLgnS8K9hDQMFU1YYEB9ofm');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $registrar = $conexion->prepare("INSERT INTO aprendices (nombre,telefono,detalles) VALUES (?, ?, ?)");
    $registrar->execute([$_POST["nom"], $_POST["tel"], $_POST["det"]]);
} catch (PDOException $e) {
    $errorPostgres = $e->getMessage();
}

# ==========================================
# 2. CONEXIÓN Y REGISTRO EN MONGO DB ATLAS
# ==========================================
require '../vendor/autoload.php'; 

try {
    $cliente = new MongoDB\Client("mongodb+srv://andresg3016_db_user:9zGxNgOxqJHtKICk@cluster0.cngc6uf.mongodb.net/?appName=Cluster0");
    $db = $cliente->Espejo; 
    $coleccion = $db->aprendices;   

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $resultado = $coleccion->insertOne([
            "nombre" => $_POST["nom"] ?? '',
            "telefono" => $_POST["tel"] ?? '',
            "detalles" => $_POST["det"] ?? ''
        ]);
    }
} catch (Exception $e) {
    $errorMongo = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Registros - Sistema Dual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5">
        
        <div class="row mb-4">
            <div class="col-12 text-center">
                <?php if (!isset($errorPostgres) && !isset($errorMongo)): ?>
                    <div class="alert alert-success d-inline-block px-5 py-3 shadow-sm" role="alert">
                        <h4 class="alert-heading m-0"><i class="bi bi-check-circle-fill me-2"></i> ¡Registro exitoso en ambas bases de datos!</h4>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger d-inline-block px-5 py-3 shadow-sm" role="alert">
                        <h4 class="alert-heading m-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Hubo un problema al registrar los datos</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
                        <h5 class="m-0"><i class="bi bi-database me-2"></i> PostgreSQL <small class="fw-light">(Relacional)</small></h5>
                        <span class="badge bg-white text-primary">SQL</span>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($errorPostgres)): ?>
                            <div class="alert alert-sm alert-danger"><?= $errorPostgres ?></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead class="table-light text-secondary text-uppercase fs-7">
                                        <tr>
                                            <th style="width: 10%">ID</th>
                                            <th>Nombre Completo</th>
                                            <th>Contacto</th>
                                            <th>Detalles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $consulta = $conexion->prepare("SELECT * FROM aprendices ORDER BY id DESC");
                                        $consulta->execute();
                                        $tablaPostgres = $consulta->fetchAll(PDO::FETCH_ASSOC);
                                        $conexion = null; // Cierre de conexión
                                        
                                        foreach($tablaPostgres as $fila): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary-subtle text-secondary fw-bold">#<?= $fila['id'] ?></span></td>
                                                <td class="fw-semibold text-dark"><?= htmlspecialchars($fila['nombre']) ?></td>
                                                <td><i class="bi bi-telephone text-muted me-1"></i> <?= htmlspecialchars($fila['telefono']) ?></td>
                                                <td class="text-muted fs-7"><?= htmlspecialchars($fila['detalles']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-success text-white py-3 d-flex align-items-center justify-content-between">
                        <h5 class="m-0"><i class="bi bi-file-earmark-code me-2"></i> MongoDB Atlas <small class="fw-light">(NoSQL - Documental)</small></h5>
                        <span class="badge bg-white text-success">NoSQL</span>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($errorMongo)): ?>
                            <div class="alert alert-sm alert-danger"><?= $errorMongo ?></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead class="table-light text-secondary text-uppercase fs-7">
                                        <tr>
                                            <th style="width: 30%">Object ID (_id)</th>
                                            <th>Nombre Completo</th>
                                            <th>Contacto</th>
                                            <th>Detalles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Traer los documentos de Mongo
                                        $documentosMongo = $coleccion->find([], ['sort' => ['_id' => -1]]);
                                        
                                        foreach($documentosMongo as $doc): ?>
                                            <tr>
                                                <td><code class="text-truncate d-inline-block text-success style-code" style="max-width: 110px;" title="<?= $doc['_id'] ?>"><?= $doc['_id'] ?></code></td>
                                                <td class="fw-semibold text-dark"><?= htmlspecialchars($doc['nombre'] ?? '') ?></td>
                                                <td><i class="bi bi-telephone text-muted me-1"></i> <?= htmlspecialchars($doc['telefono'] ?? '') ?></td>
                                                <td class="text-muted fs-7"><?= htmlspecialchars($doc['detalles'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="../index.html" class="btn btn-outline-secondary px-4 py-2"><i class="bi bi-arrow-left me-2"></i> Registrar otro aprendiz</a>
            </div>
        </div>

    </div>

    <style>
        .fs-7 { font-size: 0.85rem; }
        .style-code { font-size: 0.75rem; background-color: #f4f9f4; padding: 2px 6px; border-radius: 4px; }
    </style>
</body>
</html>
