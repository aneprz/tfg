<?php

$files = glob(__DIR__ . "/../db/migrations/*.php");

$schema = [
    "tables" => [],
    "relations" => []
];

function ensureTable(&$schema, $table, $pk = null) {

    if (!isset($schema["tables"][$table])) {
        $schema["tables"][$table] = [
            "columns" => [],
            "pk" => $pk
        ];
    }

    if ($pk) {
        $schema["tables"][$table]["pk"] = $pk;

        // asegurar PK como columna real
        $exists = array_column($schema["tables"][$table]["columns"], "name");

        if (!in_array($pk, $exists, true)) {
            $schema["tables"][$table]["columns"][] = [
                "name" => $pk,
                "type" => "INTEGER"
            ];
        }
    }
}

function addColumn(&$schema, $table, $name, $type) {

    ensureTable($schema, $table);

    $exists = array_column($schema["tables"][$table]["columns"], "name");

    if (!in_array($name, $exists, true)) {
        $schema["tables"][$table]["columns"][] = [
            "name" => $name,
            "type" => strtoupper($type)
        ];
    }
}

/**
 * 🔥 FIX CRÍTICO: separa columnas tipo "id_usuario,id_amigo"
 */
function normalizeSchema(&$schema) {

    foreach ($schema["tables"] as $tableName => &$table) {

        $fixed = [];
        $seen = [];

        foreach ($table["columns"] as $col) {

            if (strpos($col["name"], ",") !== false) {

                $parts = explode(",", $col["name"]);

                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p === "" || in_array($p, $seen, true)) continue;

                    $fixed[] = [
                        "name" => $p,
                        "type" => $col["type"]
                    ];

                    $seen[] = $p;
                }

                continue;
            }

            if (in_array($col["name"], $seen, true)) continue;

            $fixed[] = $col;
            $seen[] = $col["name"];
        }

        $table["columns"] = $fixed;
    }
}

foreach ($files as $file) {

    $lines = file($file);
    $currentTable = null;

    foreach ($lines as $line) {

        // =========================
        // TABLE + PK
        // =========================
        if (preg_match(
            "/->table\\(['\"]([^'\"]+)['\"](?:,\\s*\\[.*?'id'\\s*=>\\s*'([^'\"]+)'\\s*.*?\\])?/",
            $line,
            $m
        )) {

            $currentTable = $m[1];
            $pk = $m[2] ?? null;

            ensureTable($schema, $currentTable, $pk);
            continue;
        }

        if (strpos($line, "->create(") !== false) {
            $currentTable = null;
        }

        // =========================
        // COLUMNAS
        // =========================
        if ($currentTable && preg_match(
            "/addColumn\\(['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"]/",
            $line,
            $m
        )) {
            addColumn($schema, $currentTable, $m[1], $m[2]);
        }

        // =========================
        // PRIMARY KEY EXPLICIT
        // =========================
        if ($currentTable && strpos($line, "primary_key") !== false) {

            if (preg_match("/\\[([^\\]]+)\\]/", $line, $m)) {
                $pk = str_replace(["'", " "], "", $m[1]);
                ensureTable($schema, $currentTable, $pk);
            }
        }

        // =========================
        // FOREIGN KEYS
        // =========================
        if (preg_match(
            "/addForeignKey\\(['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"],\\s*['\"]([^'\"]+)['\"]/",
            $line,
            $m
        )) {

            $localField = $m[1];
            $refTable   = $m[2];
            $refField   = $m[3];

            if (!$currentTable) continue;

            ensureTable($schema, $currentTable);
            ensureTable($schema, $refTable);

            $localCols = array_column($schema["tables"][$currentTable]["columns"], "name");
            $refCols   = array_column($schema["tables"][$refTable]["columns"], "name");

            if (!in_array($localField, $localCols, true)) continue;
            if (!in_array($refField, $refCols, true)) continue;

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

// 🔥 APPLY FIX FINAL
normalizeSchema($schema);

// =========================
// DBML GENERATOR
// =========================
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