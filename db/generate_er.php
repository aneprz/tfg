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
        // 🔥 DETECTAR TABLA + PK PHINX REAL
        // =========================
        if (preg_match(
            "/->table\\(['\"]([^'\"]+)['\"](?:,\\s*\\[.*?'id'\\s*=>\\s*'([^'\"]+)'\\s*.*?\\])?/",
            $line,
            $m
        )) {

            $currentTable = $m[1];
            $pkFromConfig = $m[2] ?? null;

            if (!isset($schema["tables"][$currentTable])) {
                $schema["tables"][$currentTable] = [
                    "columns" => [],
                    "pk" => $pkFromConfig
                ];
            } else {
                if ($pkFromConfig) {
                    $schema["tables"][$currentTable]["pk"] = $pkFromConfig;
                }
            }

            continue;
        }

        // =========================
        // 🔥 SALIR DE CONTEXTO
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

            $exists = array_column(
                $schema["tables"][$currentTable]["columns"],
                "name"
            );

            if (!in_array($colName, $exists, true)) {
                $schema["tables"][$currentTable]["columns"][] = [
                    "name" => $colName,
                    "type" => $colType
                ];
            }
        }

        // =========================
        // 🔥 PRIMARY KEY EXPLICIT
        // =========================
        if ($currentTable && strpos($line, "primary_key") !== false) {

            if (preg_match("/\\[([^\\]]+)\\]/", $line, $m)) {
                $pk = str_replace(["'", " "], "", $m[1]);
                $schema["tables"][$currentTable]["pk"] = $pk;
            }
        }

        // =========================
        // 🔥 FALLBACK PK id_usuario / id_xxx
        // =========================
        if ($currentTable && preg_match("/addColumn\\(['\"]id[_a-zA-Z0-9]*['\"]/", $line)) {
            if (!$schema["tables"][$currentTable]["pk"]) {
                preg_match("/addColumn\\(['\"]([^'\"]+)['\"]/", $line, $m2);
                $schema["tables"][$currentTable]["pk"] = $m2[1] ?? "id";
            }
        }

        // =========================
        // 🔥 FOREIGN KEYS (ULTRA FIX FINAL)
        // =========================
        if ($currentTable && preg_match(
            "/addForeignKey\\(['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"]/",
            $line,
            $m
        )) {

            $localField = $m[1];
            $refTable   = $m[2];
            $refField   = $m[3];

            // ❌ validaciones anti basura
            if (!$localField || !$refTable || !$refField) continue;
            if (!isset($schema["tables"][$refTable])) continue;

            $refCols = array_column($schema["tables"][$refTable]["columns"], "name");
            if (!in_array($refField, $refCols, true)) {
                // si no existe columna en tabla destino, ignorar
                continue;
            }

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
 * 🔥 DBML GENERATOR CLEAN
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