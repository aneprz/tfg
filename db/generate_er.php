<?php

$files = glob(__DIR__ . "/../db/migrations/*.php");

$schema = [
    "tables" => [],
    "relations" => []
];

foreach ($files as $file) {

    $lines = file($file);
    $currentTable = null;

    foreach ($lines as $line) {

        // =========================
        // 🔥 DETECTAR TABLA REAL
        // =========================
        if (preg_match("/->table\\(['\"]([^'\"]+)['\"]/", $line, $m)) {

            $currentTable = trim($m[1]);

            if (!isset($schema["tables"][$currentTable])) {
                $schema["tables"][$currentTable] = [
                    "columns" => [],
                    "pk" => null
                ];
            }

            continue;
        }

        // =========================
        // 🔥 SALIR DE CONTEXTO (FIN DE FLUJO)
        // =========================
        if (strpos($line, "->create(") !== false) {
            $currentTable = null;
        }

        // =========================
        // 🔥 COLUMNAS
        // =========================
        if ($currentTable && preg_match(
            "/addColumn\\(['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"]/",
            $line,
            $m
        )) {

            $colName = $m[1];
            $colType = strtoupper($m[2]);

            // evitar duplicados
            $exists = array_column($schema["tables"][$currentTable]["columns"], "name");

            if (!in_array($colName, $exists, true)) {
                $schema["tables"][$currentTable]["columns"][] = [
                    "name" => $colName,
                    "type" => $colType
                ];
            }
        }

        // =========================
        // 🔥 PRIMARY KEY (Phinx real)
        // =========================
        if ($currentTable && strpos($line, "primary_key") !== false) {

            if (preg_match("/\\[([^\\]]+)\\]/", $line, $m)) {
                $pk = str_replace(["'", " "], "", $m[1]);
                $schema["tables"][$currentTable]["pk"] = $pk;
            }
        }

        // fallback PK id
        if ($currentTable && preg_match("/addColumn\\(['\"]id['\"]/", $line)) {
            if (!$schema["tables"][$currentTable]["pk"]) {
                $schema["tables"][$currentTable]["pk"] = "id";
            }
        }

        // =========================
        // 🔥 FOREIGN KEYS (FIX REAL FINAL)
        // =========================
        if ($currentTable && preg_match(
            "/addForeignKey\\(['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"]/",
            $line,
            $m
        )) {

            $localField = $m[1];
            $refTable   = $m[2];
            $refField   = $m[3];

            // ❌ anti basura
            if (!$localField || !$refTable || !$refField) continue;

            $key = $currentTable . "." . $localField . "->" . $refTable . "." . $refField;

            $schema["relations"][$key] = [
                "local_table" => $currentTable,
                "local_field" => $localField,
                "ref_table"   => $refTable,
                "ref_field"   => $refField
            ];
        }
    }
}

/**
 * =========================
 * 🔥 DBML LIMPIO FINAL
 * =========================
 */
function toDBML($schema) {

    $out = "";

    foreach ($schema["tables"] as $name => $table) {

        $out .= "Table $name {\n";

        foreach ($table["columns"] as $col) {
            $out .= "  {$col['name']} {$col['type']}\n";
        }

        if (!empty($table["pk"])) {
            $out .= "  primary key ({$table["pk"]})\n";
        }

        $out .= "}\n\n";
    }

    foreach ($schema["relations"] as $r) {

        $out .= "Ref: {$r['local_table']}.{$r['local_field']} > {$r['ref_table']}.{$r['ref_field']}\n";
    }

    return $out;
}

echo "<pre>" . toDBML($schema) . "</pre>";